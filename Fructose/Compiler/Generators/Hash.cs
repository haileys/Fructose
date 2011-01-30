using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using IronRuby.Compiler.Ast;

namespace Fructose.Compiler.Generators
{
    [Generator(NodeTypes.HashConstructor)]
    public class Hash : AstNodeGenerator
    {
        public override void Compile(Compiler compiler, Node node, NodeParent parent)
        {
            var hash = (HashConstructor)node;
            compiler.AppendLine("$_tmp_pairs[] = array();");

            foreach (var maplet in hash.Maplets)
            {
                compiler.CompileNode(maplet.Key, parent.CreateChild(node));
                compiler.AppendLine("$_tmp_pairs[count($_tmp_pairs)-1][] = array_pop($_stack);");
                compiler.CompileNode(maplet.Value, parent.CreateChild(node));
                compiler.AppendLine("$_tmp_pairs[count($_tmp_pairs)-1][] = array_pop($_stack);");
            }

            compiler.AppendLine("$_stack[] = F_Hash::__by_flatpairs(array_pop($_tmp_pairs));");
        }
    }
}
