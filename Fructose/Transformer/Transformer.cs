using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using IronRuby.Compiler;
using IronRuby.Compiler.Ast;
using Fructose.Compiler;
using Microsoft.Scripting;
using System.Diagnostics;

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
            if (currentClass == null)
            {
                throw new FructoseCompileException("Can't use blocks outside a class yet", node);
            }
            var methodname = "__lambda_" + ++blockUniqueId;
            currentClass.Body.Statements.Add(new MethodDefinition(currentClass.DefinedScope, null, methodname, node.Parameters, new Body(node.Body, null, null, null, node.Location), node.Location));
            transformations.RefactoredBlocksToMethods.Add(node, methodname);
        }
    }
}
