using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using IronRuby.Compiler.Ast;

namespace Fructose.Compiler.Generators
{
    [Generator(NodeTypes.StringLiteral)]
    public class StringLiteralGenerator : AstNodeGenerator
    {
        public override void Compile(Compiler compiler, Node node, NodeParent parent)
        {
            compiler.AppendLine("$_stack[] = F_String::__from_string('{0}');", ((StringLiteral)node).Value.ToString().Replace("\\","\\\\").Replace("'","\\'"));
        }
    }
    [Generator(NodeTypes.Literal)]
    public class LiteralGenerator : AstNodeGenerator
    {
        public override void Compile(Compiler compiler, Node node, NodeParent parent)
        {
            if (((Literal)node).Value == null)
            {
                // nil
                compiler.AppendLine("$_stack[] = new F_NilClass;");
                return;
            }
            if (((Literal)node).Value is bool)
            {
                // True or False
                compiler.AppendLine("$_stack[] = new F_{0}Class;", ((Literal)node).Value.ToString());
                return;
            }
            if (((Literal)node).Value is string)
            {
                switch (((Literal)node).Value.ToString())
                {
                    case "self":
                        compiler.AppendLine("$_stack[] = $_locals->self;", ((Literal)node).Value.ToString());
                        break;
                    case "__FILE__":
                    case "__LINE__":
                        compiler.AppendLine("$_stack[] = F_String::__from_string({0});", ((Literal)node).Value.ToString());
                        break;
                }
                return;
            }

            compiler.AppendLine("$_stack[] = F_Number::__from_number({0});", ((Literal)node).Value.ToString());
        }
    }
}
