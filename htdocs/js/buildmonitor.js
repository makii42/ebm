$(function ()
{
    var changeJobHeights,
        getJobs,
        init,
        render,

        $jobs = $('#jobs'),

        _config = new Config(configData),
        _jobs = [];


    init = function ()
    {
        $.ajaxSetup({
            cache: false
        });

        $(window).bind('resize', changeJobHeights);
        changeJobHeights();

        getJobs();

        window.setTimeout(pageRefresh, _config.get('pageRefresh'));
    };


    pageRefresh = function ()
    {
        console.log('refresh');
        window.location.reload();
        window.setTimeout(pageRefresh, _config.get('pageRefresh'));
    };


    changeJobHeights = function ()
    {
        $jobs.height($(window).height() - parseInt($('body').css('margin-top'), 10) * 2);
    };


    getJobs = function ()
    {
        var jobHosts = _config.get('jobs', []),
            jobs;

        for (var hostLabel in jobHosts)
        {
            jobs = jobHosts[hostLabel];
            for (var i = 0; i < jobs.length; i++)
            {
                _jobs.push(new Job({
                    name: jobs[i],
                    hostLabel: hostLabel,
                    config: _config.get('hosts.' + hostLabel)
                }));
            }
        }

        render(_jobs);
    };


    render = function (jobs)
    {

        var jobsHeight = parseInt($jobs.height(), 10),
            jobHeight = Math.floor(jobsHeight / jobs.length) - 2;

        $.each(jobs, function (
            index, job)
        {

            var $jobNode = job.getNode();
            $jobNode.height(jobHeight);
            $jobs.append($jobNode);
            $jobNode.find('h2').fitText(1.8);
        });

    };

    init();
});
