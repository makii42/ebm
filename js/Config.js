var Config = function (data)
{
    var get;


    get = function (key)
    {
        return data[key];
    };


    return {
        get: get
    };
};
