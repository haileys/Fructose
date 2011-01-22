using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using IronRuby.Compiler.Ast;

namespace Fructose.Compiler.Generators
{
    [Generator(NodeTypes.StringConstructor)]
    public class StringConstructorGenerator : AstNodeGenerator
    {
        public override void Compile(Compiler compiler, Node node, NodeParent parent)
        {
            var sc = (StringConstructor)node;

            switch (sc.Kind)
            {
                case StringKind.Symbol:
                    throw new NotImplementedException("Symbols not implemented yet");

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
                    if (sc.Parts.Count != 1 || sc.Parts[0].NodeType != NodeTypes.StringLiteral)
                        throw new FructoseCompileException("String interpolation not supported _yet_", node);
                    compiler.AppendLine("$_stack[] = F_String::__from_string('{0}');", ((StringLiteral)sc.Parts[0]).Value.ToString().Replace("'", "\\'"));
                    break;
            }
        }
    }
}
