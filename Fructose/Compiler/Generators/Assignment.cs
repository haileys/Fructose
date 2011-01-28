using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using IronRuby.Compiler.Ast;

namespace Fructose.Compiler.Generators
{
    [Generator(NodeTypes.SimpleAssignmentExpression)]
    public class SimpleAssignment : AstNodeGenerator
    {
        internal static string assignmentVar(LeftValue lval, NodeParent parent)
        {
            if (lval is LocalVariable)
            {
                var method = parent.OfType<MethodDefinition>().ToArray();
                if (method.Length > 0 && method[0].Parameters != null
                    && method[0].Parameters.Mandatory.Where(p => p.ToString() == ((LocalVariable)lval).Name).Count() > 0)
                {
                    return "$" + Mangling.RubyIdentifierToPHP(((LocalVariable)lval).Name);
                }
                if (parent.OfType<ClassDefinition>().Count() == 0)
                {
                    return "$_locals->" + Mangling.RubyIdentifierToPHP(((LocalVariable)lval).Name);
                }
            }

            return ((Variable)lval).ToPHPVariable();
        }

        public override void Compile(Compiler compiler, IronRuby.Compiler.Ast.Node node, NodeParent parent)
        {
            var sae = (SimpleAssignmentExpression)node;

            switch (sae.Left.NodeType)
            {
                case NodeTypes.ArrayItemAccess:
                    var aia = (ArrayItemAccess)sae.Left;
                    // substitute a method call of []= rather than the crap that ironruby's parser produces
                    compiler.CompileNode(new MethodCall(aia.Array, "[]=", new Arguments(aia.Arguments.Expressions.Concat(new[] { sae.Right }).ToArray()), sae.Location), parent);
                    return;
            }

            compiler.CompileNode(sae.Right, parent.CreateChild(node));
            compiler.AppendLine("{0} = $_stack[count($_stack)-1];", assignmentVar(sae.Left, parent));
        }
    }
    [Generator(NodeTypes.ParallelAssignmentExpression)]
    public class ParallelAssignment : AstNodeGenerator
    {
        public override void Compile(Compiler compiler, Node node, NodeParent parent)
        {
            var pa = (ParallelAssignmentExpression)node;
            if (pa.Left.HasUnsplattedValue)
                throw new FructoseCompileException("Unsplats not yet supported.", node);

            if (pa.Right.Length == 1)
            {
                compiler.CompileNode(pa.Right[0], parent.CreateChild(node));
                compiler.AppendLine("if(get_class($_stack[count($_stack)-1]) !== 'F_Array')");
                compiler.Indent();
                compiler.AppendLine("$_stack[] = F_Array::__from_array(array(array_pop($_stack)));");
                compiler.Dedent();
            }
            else
            {
                compiler.AppendLine("$ptmp = array();");
                foreach (var rval in pa.Right)
                {
                    compiler.CompileNode(rval, parent.CreateChild(node));
                    compiler.AppendLine("$ptmp[] = array_pop($_stack);");
                }
                compiler.AppendLine("$_stack[] = F_Array::__from_array($ptmp);");
            }

            var sb = new StringBuilder();
            foreach (var lval in pa.Left.LeftValues)
                sb.Append(SimpleAssignment.assignmentVar(lval, parent) + ",");
            compiler.AppendLine("@list({0}) = array_pop($_stack)->__ARRAY;", sb.ToString());

            foreach (var lval in pa.Left.LeftValues.Reverse())
                compiler.AppendLine("if({0} === NULL) {0} = new F_NilClass;", SimpleAssignment.assignmentVar(lval, parent));
        }
    }
}
