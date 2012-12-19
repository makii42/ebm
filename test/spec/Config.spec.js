describe('Config', function (subject)
{
    describe('get', function ()
    {
        it('should return simple data', function ()
        {
            var data = {
                    key: 'aSimpleValue'
                },
                config = new Config(data);

            assertSame('aSimpleValue', config.get('key'));
        });

        it('should return nested data', function ()
        {
            var data = {
                    server: {
                        host: 'hostUr'
                    }
                },
                config = new Config(data);

            assertSame('hostUr', config.get('server.host'));
        })
    });
});