using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.Mvc;

namespace Webhook.Controllers
{
    public class APIController : Controller
    {
        // GET: API
        public ActionResult Index()
        {

            if (HttpContext.Request.HttpMethod == "POST")
            {
                var requestBody = Request.InputStream;
                var jsonResult = Json(requestBody, JsonRequestBehavior.AllowGet).ToString();
                var jsonObject = JsonConvert.DeserializeObject<Dictionary<string, dynamic>>(jsonResult);
                var text = jsonObject["result"]["fulfillment"]["speech"];
                var finalResult = new { speech = text, displayText = text, source = "webhook" };
                var result = Json(finalResult);
                ViewBag.result = result;
                Console.WriteLine(result);
            }
            else
            {
                Console.WriteLine("Method not allowed");
                ViewBag.result = "Method";
            }
            return View();
        }
    }
}