using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using IronRuby.Compiler.Ast;

namespace Fructose.Compiler.Generators
{
    [Generator(NodeTypes.SymbolLiteral)]
    public class SymbolLiteralGenerator : AstNodeGenerator
    {
        public override void Compile(Compiler compiler, Node node, NodeParent parent)
        {
            compiler.AppendLine("$_stack[] = F_Symbol::__from_string('{0}');", ((SymbolLiteral)node).Value.ToString());
        }
    }

    [Generator(NodeTypes.StringConstructor)]
    public class StringConstructorGenerator : AstNodeGenerator
    {
        public override void Compile(Compiler compiler, Node node, NodeParent parent)
        {
            var sc = (StringConstructor)node;

            switch (sc.Kind)
            {
                case StringKind.Symbol:
                    if (sc.Parts.Count != 1 || sc.Parts[0].NodeType != NodeTypes.StringLiteral)
                        throw new FructoseCompileException("Symbol interpolation not supported _yet_", node);
                    compiler.AppendLine("$_stack[] = F_Symbol::__from_string('{0}');", ((StringLiteral)sc.Parts[0]).Value.ToString().Replace("'", "\\'"));
                    break;

                case StringKind.Command:
                    if (sc.Parts.Count != 1)
                        throw new FructoseCompileException("`` is escape-to-PHP operator, and only works with a string literal.", node);

                    if (sc.Parts[0].NodeType != NodeTypes.StringLiteral)
                        throw new FructoseCompileException("`` is escape-to-PHP operator, and only works with a string literal.", node);

                    compiler.AppendLine("// BEGIN: escape-to-PHP");
                    compiler.AppendLine(((StringLiteral)sc.Parts[0]).Value.ToString());
                    compiler.AppendLine("// END: escape-to-PHP");
                    break;

                case StringKind.Mutable:
                    foreach (var part in ((IEnumerable<Expression>)sc.Parts).Reverse())
                        compiler.CompileNode(part, parent.CreateChild(node));

                    compiler.AppendLine("$_stack[] = F_String::__from_string('');");

                    for(int i = 0; i < sc.Parts.Count; i++)
                        compiler.AppendLine("$_stack[] = array_pop($_stack)->__operator_lshift(NULL, array_pop($_stack));");

                    break;
            }
        }
    }
}
