var Config = function (data)
{
    const ERROR_WRONG_KEY = 'RTFA, du Fritte';

    var get,
        getNestedValue,
        getSimpleValue;


    /**
     * @param {String} key
     * @return {*}
     */
    get = function (key)
    {
        if (!key)
        {
            throw new Error(ERROR_WRONG_KEY);
        }

        if (key.match(/\./))
        {
            return getNestedValue(key, data)
        }
        return getSimpleValue(key, data);
    };


    /**
     * @param {String} key
     * @param {Object} dataSubset
     * @return {*}
     */
    getNestedValue = function (key, dataSubset)
    {
        var parts = key.split('.'),
            firstPart = parts[0];

        if (dataSubset[firstPart] && parts.length > 1)
        {
            return getNestedValue(parts.slice(1).join('.'), dataSubset[firstPart]);
        }

        return getSimpleValue(firstPart, dataSubset);
    };


    /**
     * @param {String} key
     * @param {Object} data
     * @return {*}
     */
    getSimpleValue = function (key, data)
    {
        if (!key)
        {
            throw new Error('RTFA, du Fritte');
        }

        return data[key];
    };


    return {
        ERROR_WRONG_KEY: ERROR_WRONG_KEY,
        get: get
    };
};
