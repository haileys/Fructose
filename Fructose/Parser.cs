using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

using IronRuby;
using IronRuby.Compiler;
using IronRuby.Builtins;
using IronRuby.Compiler.Ast;
using Microsoft.Scripting;
using Microsoft.Scripting.Hosting;
using Microsoft.Scripting.Hosting.Providers;

namespace Fructose
{
    public class Parser
    {
        SourceUnitTree ast;
        ScriptSource source;
        ErrorSink errorSink;
        Transformer.Transformations transformations;

        public Parser(string Source) : this(Source, ErrorSink.Default) { }
        public Parser(string Source, ErrorSink errorSink)
        {
            source = Ruby.CreateRuntime().GetEngine("rb").CreateScriptSourceFromString(Source);
            this.errorSink = errorSink;
        }

        public void Parse()
        {
            var srcunit = HostingHelpers.GetSourceUnit(source);
            var parser = new IronRuby.Compiler.Parser(errorSink);
            ast = parser.Parse(srcunit, new RubyCompilerOptions(), errorSink);

            var transformer = new Transformer.Transformer(ast);
            transformations = transformer.Transform();
        }

        public string CompileToPHP(string source = null)
        {
            var compiler = new Compiler.Compiler(ast, source);
            return compiler.Compile(transformations);
        }
    }
}
