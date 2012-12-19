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
    });
});
