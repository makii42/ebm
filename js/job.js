var Job = function (data) {

    var calculateBuildPercentage,
        calculateRemainingBuildTime,
        calculateRuntime,
        displayBuildProgress,
        displayCulprits,
        displayVcsInformation,
        fetchRemainingBuildTime,
        formatSeconds,
        getBuildStateClassByColor,
        getBuildStateClassByState,
        getNode,
        isBuildSuccess,
        trimText,

        _apiUrl = data.url + 'lastBuild/api/json',
        _building,
        _jobTemplate = '<div><div class="jobname"><h2></h2></div><div class="jobinfo"></div><div>',
        _vcsInfoTemplate = '<div class="vcs-info"><ul></ul></div>',
        _vcsInfoTrimLength = 70,
        _vcsInfoFitTextKompressor = 6.8,
        _culpritTemplate = '<div class="culprits"><h3>The usual suspect(s):</h3><ul></ul></div>',
        _culpritFitTextKompressor = 5.2,
        _pollingTimerBuilding = 1000,
        _pollingTimerNotBuilding = 5000,
        _nextBuildNumber = data.nextBuildNumber,
        _currentBuildNumber = (_nextBuildNumber-1),
        _lastCommitRevision = 0,
        _tempBuildNumber = 0,
        _defaultTrimLength = 70,

        $job = $(_jobTemplate);

    getBuildStateClassByColor = function (color) {
        return color === 'blue' ? 'success' : 'failed';
    };

    getBuildStateClassByState = function (state) {
        return state ? 'success' : 'failed';
    };

    getNode = function () {

        $job.addClass('job')
            .addClass(getBuildStateClassByColor(data.color));
        $job.find('h2').text(data.displayName);

        fetchRemainingBuildTime();

        return $job;
    };

    fetchRemainingBuildTime = function () {

        var building = false;
        $.ajax({
            url: _apiUrl,
            dataType: 'json',
            async: true,
            cache: true,
            ifModified: true,
            success: function (response) {

                $job.find('.build-progress').remove();
                if (_building === false) {
                    $job.removeClass('building');
                    _building = false;
                }

                if (response.building) {

                    $job.toggleClass('building');

                    displayBuildProgress(response);

                    _building = true;
                    window.setTimeout(fetchRemainingBuildTime, _pollingTimerBuilding);

                } else {

                    var buildState = isBuildSuccess(response.result);
                    if (!buildState) {
                        displayCulprits(response.culprits);
                    } else {
                        $job.find('.culprits').remove();
                    }
                    displayVcsInformation(response);

                    $job.removeClass('failed').removeClass('success').addClass(getBuildStateClassByState(buildState));

                    _building = false;
                    window.setTimeout(fetchRemainingBuildTime, _pollingTimerNotBuilding);
                }
            }
        });
    };

    calculateRemainingBuildTime = function (estimatedDuration, runtime) {
        var remainingBuildTime = estimatedDuration - runtime;
        return (remainingBuildTime > 0) ? remainingBuildTime : 0;
    };

    calculateBuildPercentage = function (estimatedDuration, runtime) {
        var percentage = Math.floor(runtime / estimatedDuration * 100);
        return (percentage < 100) ? percentage : 100;
    };

    calculateRuntime = function (buildStart) {
        return new Date().getTime() - buildStart;
    };

    formatSeconds = function (remainingMilliseconds) {
        var remainingSeconds = Math.floor(remainingMilliseconds / 1000),
            hours = Math.floor(remainingSeconds / 3600),
            minutes = Math.floor(remainingSeconds % 3600 / 60),
            seconds = remainingSeconds % 3600 % 60,
            output = [
                seconds + 's'
            ];

        if (minutes) {
            output.push(minutes + 'm');
        }
        if (hours) {
            output.push(hours + 'h');
        }

        return output.reverse().join('');
    };

    displayBuildProgress = function (response) {
        var runtime = calculateRuntime(response.timestamp),
            remainingMilliseconds = calculateRemainingBuildTime(response.estimatedDuration, runtime),
            $duration = $('<span></span>').addClass('build-progress');

        $duration.text(' ' + formatSeconds(remainingMilliseconds) + ' / ' +
            calculateBuildPercentage(response.estimatedDuration, runtime) + '%');

        $job.find('.jobname h2').append($duration);
    };

    displayCulprits = function (culprits) {

        $job.find('.culprits').remove();

        if (culprits.length) {
            var $culprits = $(_culpritTemplate);
            $.each(culprits, function (index, culprit) {
                $culprits.find('ul').append($('<li></li>').text(culprit.fullName));
            });
            $job.find('.jobname').append($culprits);
            $job.find('.culprits').fitText(_culpritFitTextKompressor);
        }
    };

    displayVcsInformation = function (response) {

        if (response.changeSet.items.length) {
            var $vcsInfo = $(_vcsInfoTemplate),
                tempRevision = 0;
            $.each(response.changeSet.items.slice(0, 2), function (index, vcsInfo) {
                if (vcsInfo.revision > tempRevision) {
                    tempRevision = vcsInfo.revision;
                }
                $vcsInfo.find('ul').append($('<li></li>').html(
                    trimText(vcsInfo.revision + ' [' + vcsInfo.user[0].toUpperCase() + '] ' + vcsInfo.msg, _vcsInfoTrimLength)
                ));
            });

            _tempBuildNumber = 0;

            if (_lastCommitRevision <= tempRevision) {
                _lastCommitRevision = tempRevision;
                $job.find('.jobinfo').html($vcsInfo);
                $job.find('.vcs-info').fitText(_vcsInfoFitTextKompressor);
            }
        } else {
            _tempBuildNumber = (_tempBuildNumber == 0) ? _currentBuildNumber : _tempBuildNumber;
            _tempBuildNumber--;
            
            $.ajax({
                url: data.url + _tempBuildNumber + '/api/json',
                dataType: 'json',
                async: true,
                cache: true,
                ifModified: true,
                success: function (response) {
                    displayVcsInformation(response);
                }
            });
        }
    };
    

    isBuildSuccess = function (result) {
        return result === 'SUCCESS';
    };

    trimText = function (text, length) {
        var length = length || _defaultTrimLength;
        return (text.length > length) ? text.substr(0, length - 3) + '...' : text;
    };

    return {
        getNode:getNode
    };
};
