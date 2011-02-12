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
			
			string rstr = "";
			if(regex.Options.HasFlag(RubyRegexOptions.IgnoreCase))
				rstr += "i";
			if(regex.Options.HasFlag(RubyRegexOptions.Multiline))
				rstr += "m";
			if(regex.Options.HasFlag(RubyRegexOptions.Extended))
				rstr += "x";
			
			compiler.CompileNode(new StringConstructor(regex.Pattern, StringKind.Mutable, regex.Location));
			
			compiler.AppendLine("$_stack[] = F_Regexp::SF_new(NULL, array_pop($_stack), F_String::__from_string('{0}'));", rstr);
        }
    }
	
    [Generator(NodeTypes.RegexMatchReference)]
    public class RegexMatch : AstNodeGenerator
    {
        public override void Compile(Compiler compiler, Node node, NodeParent parent)
        {
			var m = (RegexMatchReference)node;
			compiler.AppendLine("$_stack[] = F_Regexp::_get_match({0});", m.VariableName);
		}
	}
}