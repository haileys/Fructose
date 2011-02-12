using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using IronRuby.Compiler.Ast;
using IronRuby.Builtins;

namespace Fructose.Compiler.Generators
{
    [Generator(NodeTypes.RegularExpression)]
    public class Regex : AstNodeGenerator
    {
        public override void Compile(Compiler compiler, Node node, NodeParent parent)
        {
            var regex = (RegularExpression)node;
			if(regex.Pattern.Count != 1)
				throw new FructoseCompileException("Multiple Patterns in a regex isn't supported. If you run into this error, *please* open a GitHub issue with the code you entered to get this exception", node);
			
			string rstr = "/" + ((StringLiteral)regex.Pattern[0]).Value.ToString() + "/";
			if(regex.Options.HasFlag(RubyRegexOptions.IgnoreCase))
				rstr += "i";
			if(regex.Options.HasFlag(RubyRegexOptions.Multiline))
				rstr += "m";
			if(regex.Options.HasFlag(RubyRegexOptions.Extended))
				rstr += "x";
			
			compiler.AppendLine("$_stack[] = F_Regexp::__from_string('{0}');", rstr.Replace("'", "\\'"));
        }
    }
}