var Config = function (data)
{
    var get,
        getNestedValue,
        getSimpleValue;


    get = function (key)
    {
        if (key.match(/\./))
        {
            return getNestedValue(key, data)
        }
        return getSimpleValue(key, data);
    };


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


    getSimpleValue = function (key, data)
    {
        return data[key];
    };


    return {
        get: get
    };
};
