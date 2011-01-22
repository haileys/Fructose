using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using IronRuby.Compiler.Ast;

namespace Fructose.Compiler
{
    public class FructoseCompileException : Exception
    {
        public FructoseCompileException(string Message, Node node)
            : base(string.Format("{0} at line {1}, col {2}", Message, node.Location.Start.Line, node.Location.Start.Column))
        { }
    }
}
