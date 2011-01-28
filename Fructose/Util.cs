using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Security.Cryptography;

namespace Fructose
{
    public static class Util
    {
        public static string MD5(this string obj)
        {
            var digest = new MD5CryptoServiceProvider().ComputeHash(Encoding.UTF8.GetBytes(obj));
            StringBuilder sb = new StringBuilder();
            foreach (var b in digest)
                sb.Append(b.ToString("x2"));
            return sb.ToString();
        }
    }
}
