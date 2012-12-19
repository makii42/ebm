define(['js/Config.js'], function ()
{
    describe('get', function ()
    {
        it('should return simple data', function ()
        {
            var data = {
                    key: 'aSimpleValue'
                },
                config = new Config(data);

            expect(config.get('key')).toBe('aSimpleValue');
        });

        it('should return nested data', function ()
        {
            var data = {
                    server: {
                        host: 'hostUrl'
                    }
                },
                config = new Config(data);

            expect(config.get('server.host')).toBe('hostUrl');
        });

//        it('should return it`s default value for non-existing keys', function ()
//        {
//            var config = new Config({});
//
//            expect(config.get('iDoNotExist')).toBe(null);
//        });

        it('should return it`s default value for invalid keys', function ()
        {
            var config = new Config({});
        });

        // @todo: Put in datasets (if possible)
        it('should throw an error for wrong keys', function ()
        {
            var config = new Config({}),
                thrownException,
                tryCatch = function (key)
                {
                    try
                    {
                        config.get(key);
                    } catch (e)
                    {
                        thrownException = e;
                    }
                    expect(thrownException.message).toEqual(config.ERROR_WRONG_KEY);
                };

            tryCatch('');
            tryCatch(null);
            tryCatch(undefined);
            tryCatch('....');
        });
    });
});
