using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using IronRuby.Compiler;
using IronRuby.Compiler.Ast;
using Fructose.Compiler;
using Microsoft.Scripting;
using System.Diagnostics;
using System.Reflection;

namespace Fructose.Transformer
{
    public class Transformations
    {
        public Dictionary<BlockDefinition, string> RefactoredBlocksToMethods { get; private set; }

        public Transformations()
        {
            RefactoredBlocksToMethods = new Dictionary<BlockDefinition, string>();
        }
    }
    public class Transformer : IronRuby.Compiler.Ast.Walker
    {
        public SourceUnitTree AST { get; private set; }
        ClassDefinition currentClass;
        Transformations transformations = new Transformations();
        int blockUniqueId = 0;

        public Transformer(SourceUnitTree AST)
        {
            this.AST = AST;
        }
        public Transformations Transform()
        {
            base.Walk(AST);
            return transformations;
        }
        protected override void Walk(MethodCall node)
        {
            if (node.Block != null)
                base.Walk(node.Block);

            base.Walk(node);
        }
        public override bool Enter(ClassDefinition node)
        {
            currentClass = node;
            return base.Enter(node);
        }
        public override void Exit(ClassDefinition node)
        {
            currentClass = null;
            base.Exit(node);
        }
        protected override void Walk(BlockDefinition node)
        {
            var statements = currentClass == null ? AST.Statements : currentClass.Body.Statements;
            var methodname = "__lambda_" + ++blockUniqueId;

            LexicalScope scope = currentClass != null ? currentClass.DefinedScope
                : /* HACK HACK HACK */
                (LexicalScope)AST.GetType().GetField("_definedScope", BindingFlags.Instance | BindingFlags.NonPublic).GetValue(AST);

            statements.Add(new MethodDefinition(scope, null, methodname, node.Parameters, new Body(node.Body, null, null, null, node.Location), node.Location));
            transformations.RefactoredBlocksToMethods.Add(node, methodname);
        }
    }
}
