using Microsoft.Owin;
using Owin;

[assembly: OwinStartupAttribute(typeof(Webhook.Startup))]
namespace Webhook
{
    public partial class Startup
    {
        public void Configuration(IAppBuilder app)
        {
            ConfigureAuth(app);
        }
    }
}
