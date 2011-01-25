using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using IronRuby.Compiler.Ast;
using Microsoft.Scripting;

namespace Fructose.Compiler.Generators
{
    [Generator(NodeTypes.WhileLoopExpression)]
    public class While : AstNodeGenerator
    {
        public override void Compile(Compiler compiler, Node node, NodeParent parent)
        {
            compiler.AppendLine("while(true)");
            compiler.AppendLine("{");
            compiler.Indent();
            
            if(((WhileLoopExpression)node).IsPostTest)
            {
                foreach(var stmt in ((WhileLoopExpression)node).Statements)
                    compiler.CompileNode(stmt, parent.CreateChild(node));
            }

            if (((WhileLoopExpression)node).IsWhileLoop)
                compiler.CompileNode(((WhileLoopExpression)node).Condition, parent.CreateChild(node));
            else
                compiler.CompileNode(new NotExpression(((WhileLoopExpression)node).Condition, node.Location), parent.CreateChild(node));

            compiler.AppendLine("$_cond = array_pop($_stack);");
            compiler.AppendLine("if(get_class($_cond) === 'F_NilClass' || get_class($_cond) === 'F_FalseClass' || is_subclass_of($_cond, 'F_NilClass') || is_subclass_of($_cond, 'F_FalseClass'))");
            compiler.AppendLine("{");
            compiler.Indent();
            compiler.AppendLine("break;");
            compiler.Dedent();
            compiler.AppendLine("}");

            if (!((WhileLoopExpression)node).IsPostTest)
            {
                foreach (var stmt in ((WhileLoopExpression)node).Statements)
                    compiler.CompileNode(stmt, parent.CreateChild(node));
            }

            compiler.Dedent();
            compiler.AppendLine("}");
        }
    }
}
