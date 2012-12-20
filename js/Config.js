var Config = function (data)
{
    const ERROR_WRONG_KEY = 'RTFA, du Fritte';

    var get,
        getNestedValue,
        getSimpleValue;


    /**
     * @param {String} key
     * @param {*} defaultValue
     * @return {*}
     */
    get = function (key, defaultValue)
    {
        if (!key)
        {
            throw new Error(ERROR_WRONG_KEY);
        }

        if (key.match(/\./))
        {
            return getNestedValue(key, data, defaultValue || null)
        }
        return getSimpleValue(key, data, defaultValue || null);
    };


    /**
     * @param {String} key
     * @param {Object} dataSubset
     * @param {*} defaultValue
     * @return {*}
     */
    getNestedValue = function (key, dataSubset, defaultValue)
    {
        var parts = key.split('.'),
            firstPart = parts[0];

        if (dataSubset[firstPart] && parts.length > 1)
        {
            return getNestedValue(parts.slice(1).join('.'), dataSubset[firstPart], defaultValue);
        }

        return getSimpleValue(firstPart, dataSubset, defaultValue);
    };


    /**
     * @param {String} key
     * @param {Object} data
     * @param {*} defaultValue
     * @return {*}
     */
    getSimpleValue = function (key, data, defaultValue)
    {
        if (!key)
        {
            throw new Error('RTFA, du Fritte');
        }

        var value = data[key];
        return typeof value === 'undefined' ? defaultValue : value;
    };


    return {
        ERROR_WRONG_KEY: ERROR_WRONG_KEY,
        get:             get
    };
};
