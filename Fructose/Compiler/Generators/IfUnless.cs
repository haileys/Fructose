using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using IronRuby.Compiler.Ast;
using Microsoft.Scripting;

namespace Fructose.Compiler.Generators
{
    [Generator(NodeTypes.UnlessExpression)]
    public class Unless : AstNodeGenerator
    {
        public override void Compile(Compiler compiler, Node node, NodeParent parent)
        {
            var unless = (UnlessExpression)node;
            compiler.CompileNode(new IfExpression(new NotExpression(unless.Condition, unless.Condition.Location), unless.Statements, new List<ElseIfClause> { unless.ElseClause }, unless.Location), parent.CreateChild(node));
        }
    }

    [Generator(NodeTypes.ConditionalStatement)]
    public class Conditional : AstNodeGenerator
    {
        public override void Compile(Compiler compiler, Node node, NodeParent parent)
        {
            var cond = (ConditionalStatement)node;
            Expression cond_expr = cond.Condition;
            if(cond.IsUnless)
                cond_expr = new NotExpression(cond_expr, cond.Location);

            var elseif = cond.ElseStatement != null
                ? new List<ElseIfClause>() { new ElseIfClause(null, new Statements(cond.ElseStatement), cond.ElseStatement.Location) }
                : new List<ElseIfClause>();

            compiler.CompileNode(new IfExpression(cond_expr, new Statements(cond.Body), elseif, cond.Location));
        }
    }

    [Generator(NodeTypes.IfExpression)]
    public class If : AstNodeGenerator
    {
        public override void Compile(Compiler compiler, Node node, NodeParent parent)
        {
            compiler.CompileNode(((IfExpression)node).Condition, parent.CreateChild(node));

            compiler.AppendLine("$_cond = array_pop($_stack);");
            compiler.AppendLine("if(get_class($_cond) !== 'F_NilClass' && get_class($_cond) !== 'F_FalseClass' && !is_subclass_of($_cond, 'F_NilClass') && !is_subclass_of($_cond, 'F_FalseClass'))");
            compiler.AppendLine("{");
            compiler.Indent();

            foreach (var stmt in ((IfExpression)node).Body)
                compiler.CompileNode(stmt, parent.CreateChild(node));

            compiler.Dedent();
            compiler.AppendLine("}");

            if (((IfExpression)node).ElseIfClauses.Count > 0)
            {
                compiler.AppendLine("else");
                compiler.AppendLine("{");
                compiler.Indent();

                var firstelseif = ((IfExpression)node).ElseIfClauses.First();
                var rest = ((IfExpression)node).ElseIfClauses.Skip(1).ToList();
                if (firstelseif.Condition == null)
                {
                    foreach (var stmt in firstelseif.Statements)
                        compiler.CompileNode(stmt, parent.CreateChild(node));
                }
                else
                {
                    compiler.CompileNode(new IfExpression(firstelseif.Condition, firstelseif.Statements, rest,
                        new SourceSpan(firstelseif.Location.Start, (rest.Count > 0 ? rest.Last() : firstelseif).Location.End)), parent.CreateChild(node));
                }

                compiler.Dedent();
                compiler.AppendLine("}");
            }
        }
    }
}
