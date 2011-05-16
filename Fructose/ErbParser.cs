using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

using IronRuby;
using IronRuby.Compiler;
using Microsoft.Scripting;
using Microsoft.Scripting.Hosting;

namespace Fructose
{
    public class ErbParser : Parser
    {
        public ErbParser(string Source) : this(Source, ErrorSink.Default) { }
        public ErbParser(string Source, ErrorSink errorSink) : base(ErbToRb(Source), errorSink) { }

        static dynamic _erbCompiler;
        static string ErbToRb(string str)
        {
            if (_erbCompiler == null)
            {
                var engine = Ruby.CreateRuntime().GetEngine("rb");
                engine.Execute(@"
require 'erb'

class ErbCompiler
  def compile(str)
    ERB.new(str.to_s).src + ""\n\nputs _erbout""
  end
end
                ");

                dynamic ruby = engine.Runtime.Globals;
                _erbCompiler = ruby.ErbCompiler.@new();
            }

            return _erbCompiler.compile(str).ToString();
        }
    }
}
