<?php

declare(strict_types=1);

namespace Ghostwriter\Parser\Node\Sorter;

use Ghostwriter\Parser\Node\Comparator\NodeComparator;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Const_;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignOp\Concat;
use PhpParser\Node\Expr\AssignOp\Plus;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\BinaryOp\Concat as PhpParserConcat;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\Greater;
use PhpParser\Node\Expr\BinaryOp\GreaterOrEqual;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\Minus;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BinaryOp\Smaller;
use PhpParser\Node\Expr\BinaryOp\SmallerOrEqual;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Cast\Int_;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Clone_;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ClosureUse;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\ErrorSuppress;
use PhpParser\Node\Expr\Exit_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Include_;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\Match_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PostDec;
use PhpParser\Node\Expr\PostInc;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\UnaryMinus;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\MatchArm;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\PropertyItem;
use PhpParser\Node\Scalar\Encapsed;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\MagicConst\Dir;
use PhpParser\Node\Scalar\MagicConst\File;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Continue_;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\DeclareDeclare;
use PhpParser\Node\Stmt\Echo_;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\EnumCase;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\InlineHTML;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Static_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\Node\Stmt\TryCatch;
use PhpParser\Node\Stmt\Unset_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\While_;
use PhpParser\Node\UseItem;

use function usort;

final readonly class NodeSorter
{
    public function __construct(
        private NodeComparator $nodeComparer,
    ) {}

    /**
     * @param Node[] $nodes
     *
     * @return Node[]
     */
    public function sort(array $nodes): array
    {
        usort($nodes, fn (Node $left, Node $right): int => $this->compare($left, $right));

        return $nodes;
    }

    public function sortAttributeGroup(AttributeGroup $attributeGroup): AttributeGroup
    {
        usort($attributeGroup->attrs, fn (Node $left, Node $right): int => $this->compare($left, $right));

        return $attributeGroup;
    }

    public function sortClass(Class_ $class): Class_
    {
        usort($class->attrGroups, fn (Node $left, Node $right): int => $this->compare($left, $right));
        usort($class->implements, fn (Node $left, Node $right): int => $this->compare($left, $right));
        usort($class->stmts, fn (Node $left, Node $right): int => $this->compare($left, $right));

        return $class;
    }

    public function sortClassConst(ClassConst $classConst): ClassConst
    {
        usort($classConst->attrGroups, fn (Node $left, Node $right): int => $this->compare($left, $right));
        usort($classConst->consts, fn (Node $left, Node $right): int => $this->compare($left, $right));

        return $classConst;
    }

    public function sortClassConstFetch(ClassConstFetch $classConstFetch): ClassConstFetch
    {
        return $classConstFetch;
    }

    public function sortClassLike(ClassLike $classLike): ClassLike
    {
        usort($classLike->attrGroups, fn (Node $left, Node $right): int => $this->compare($left, $right));
        usort($classLike->stmts, fn (Node $left, Node $right): int => $this->compare($left, $right));

        return $classLike;
    }

    public function sortClassMethod(ClassMethod $classMethod): ClassMethod
    {
        usort($classMethod->attrGroups, fn (Node $left, Node $right): int => $this->compare($left, $right));
        usort($classMethod->params, fn (Node $left, Node $right): int => $this->compare($left, $right));

        return $classMethod;
    }

    public function sortEnum(Enum_ $enum): Enum_
    {
        usort($enum->attrGroups, fn (Node $left, Node $right): int => $this->compare($left, $right));
        usort($enum->implements, fn (Node $left, Node $right): int => $this->compare($left, $right));
        usort($enum->stmts, fn (Node $left, Node $right): int => $this->compare($left, $right));

        return $enum;
    }

    public function sortEnumCase(EnumCase $enumCase): EnumCase
    {
        usort($enumCase->attrGroups, fn (Node $left, Node $right): int => $this->compare($left, $right));

        return $enumCase;
    }

    public function sortExpression(Expression $expression): Expression
    {
        return $expression;
    }

    public function sortForeach(Foreach_ $foreach): Foreach_
    {
        usort($foreach->stmts, fn (Node $left, Node $right): int => $this->compare($left, $right));

        return $foreach;
    }

    public function sortFuncCall(FuncCall $funcCall): FuncCall
    {
        usort($funcCall->args, fn (Node $left, Node $right): int => $this->compare($left, $right));

        return $funcCall;
    }

    public function sortFunction(Function_ $function): Function_
    {
        usort($function->attrGroups, fn (Node $left, Node $right): int => $this->compare($left, $right));
        usort($function->params, fn (Node $left, Node $right): int => $this->compare($left, $right));
        usort($function->stmts, fn (Node $left, Node $right): int => $this->compare($left, $right));

        return $function;
    }

    public function sortGroupUse(GroupUse $groupUse): GroupUse
    {
        usort($groupUse->uses, fn (UseItem $left, UseItem $right): int => $this->compare($left, $right));

        return $groupUse;
    }

    public function sortIdentical(Identical $identical): Identical
    {
        return $identical;
    }

    public function sortIf(If_ $if): If_
    {
        usort($if->elseifs, fn (Node $left, Node $right): int => $this->compare($left, $right));
        usort($if->stmts, fn (Node $left, Node $right): int => $this->compare($left, $right));

        return $if;
    }

    public function sortInstanceof(Instanceof_ $instanceof): Instanceof_
    {
        return $instanceof;
    }

    public function sortInterface(Interface_ $interface): Interface_
    {
        usort($interface->attrGroups, fn (Node $left, Node $right): int => $this->compare($left, $right));
        usort($interface->stmts, fn (Node $left, Node $right): int => $this->compare($left, $right));

        return $interface;
    }

    public function sortMatch(Match_ $match): Match_
    {
        usort($match->arms, fn (MatchArm $left, MatchArm $right): int => $this->compare($left, $right));

        return $match;
    }

    public function sortMatchArm(MatchArm $matchArm): MatchArm
    {
        if (null === $matchArm->conds) {
            return $matchArm;
        }

        usort($matchArm->conds, fn (Expr $left, Expr $right): int => $this->compare($left, $right));

        return $matchArm;
    }

    public function sortMethodCall(MethodCall $methodCall): MethodCall
    {
        usort($methodCall->args, fn (Node $left, Node $right): int => $this->compare($left, $right));

        return $methodCall;
    }

    public function sortName(Name $name): Name
    {
        return $name;
    }

    public function sortNamespace(Namespace_ $namespace): Namespace_
    {
        usort($namespace->stmts, fn (Node $left, Node $right): int => $this->compare($left, $right));

        return $namespace;
    }

    public function sortNode(Node $node): Node
    {
        return match (true) {
            //            $node instanceof PostDec => $this->sortPostDec($node),
            //            $node instanceof Break_ => $this->sortBreak($node),
            //            $node instanceof Concat => $this->sortAssignOpConcat($node),
            //            $node instanceof Encapsed => $this->sortEncapsed($node),
            //            $node instanceof For_ => $this->sortFor($node),
            //            $node instanceof SmallerOrEqual => $this->sortBinaryOpSmallerOrEqual($node),
            //            $node instanceof PostInc => $this->sortPostInc($node),
            //            $node instanceof Echo_ => $this->sortEcho($node),
            //            $node instanceof NullableType => $this->sortNullableType($node),
            //            $node instanceof NotIdentical => $this->sortBinaryOpNotIdentical($node),
            //            $node instanceof Unset_ => $this->sortUnset($node),
            //            $node instanceof Const_ => $this->sortConst($node),
            //            $node instanceof Expr\Cast\Array_ => $this->sortCastArray($node),
            //            $node instanceof Greater => $this->sortBinaryOpGreater($node),
            //            $node instanceof ErrorSuppress => $this->sortErrorSuppress($node),
            //            $node instanceof Expr\Cast\String_ => $this->sortCastString($node),
            //            $node instanceof Smaller => $this->sortBinaryOpSmaller($node),
            //            $node instanceof ElseIf_ => $this->sortElseIf($node),
            //            $node instanceof Int_ => $this->sortCastInt($node),
            //            $node instanceof Expr\BinaryOp\Plus => $this->sortBinaryOpPlus($node),
            //            $node instanceof UnaryMinus => $this->sortUnaryMinus($node),
            //            $node instanceof Plus => $this->sortAssignOpPlus($node),
            //            $node instanceof Minus => $this->sortBinaryOpMinus($node),
            //            $node instanceof Else_ => $this->sortElse($node),
            //            $node instanceof Dir => $this->sortDir($node),
            //            $node instanceof Array_ => $this->sortArray($node),
            //            $node instanceof Assign => $this->sortAssign($node),
            //            $node instanceof ArrayItem => $this->sortArrayItem($node),
            //            $node instanceof File => $this->sortFile($node),
            //            $node instanceof Attribute => $this->sortAttribute($node),
            //            $node instanceof BinaryOpConcat => $this->sortBinaryOpConcat($node),
            //            $node instanceof New_ => $this->sortNew($node),
            $node instanceof PropertyItem => $this->sortPropertyItem($node),
            //            $node instanceof BooleanNot => $this->sortBooleanNot($node),
            //            $node instanceof Equal => $this->sortBinaryOpEqual($node),
            //            $node instanceof Catch_ => $this->sortCatch($node),
            $node instanceof ArrayDimFetch => $this->sortArrayDimFetch($node),
            //            $node instanceof Isset_ => $this->sortIsset($node),
            //            $node instanceof Coalesce => $this->sortCoalesce($node),
            //            $node instanceof Throw_ => $this->sortThrow($node),
            //            $node instanceof StaticCall => $this->sortStaticCall($node),
            //            $node instanceof Exit_ => $this->sortExit($node),
            //            $node instanceof ConstFetch => $this->sortConstFetch($node),
            $node instanceof Arg => $this->sortArg($node),
            //            $node instanceof Clone_ => $this->sortClone($node),
            //            $node instanceof BooleanOr => $this->sortBinaryOpBooleanOr($node),
            //            $node instanceof Continue_ => $this->sortContinue($node),
            //            $node instanceof GreaterOrEqual => $this->sortBinaryOpGreaterOrEqual($node),
            //            $node instanceof BooleanAnd => $this->sortBooleanAnd($node),
            //            $node instanceof ClosureUse => $this->sortClosureUse($node),
            //            $node instanceof Include_ => $this->sortInclude($node),
            //            $node instanceof Variable => $this->sortVariable($node),
            //            $node instanceof Closure => $this->sortClosure($node),
            //            $node instanceof LNumber => $this->sortLNumber($node),
            //            $node instanceof Identifier => $this->sortIdentifier($node),
            //            $node instanceof DeclareDeclare => $this->sortDeclareDeclare($node),
            //            $node instanceof Declare_ => $this->sortDeclare($node),
            //            $node instanceof InlineHTML => $this->sortInlineHTML($node),
            //            $node instanceof Foreach_ => $this->sortForeach($node),
            //            $node instanceof TryCatch => $this->sortTryCatch($node),
            $node instanceof ClassConst => $this->sortClassConst($node),
            $node instanceof ClassMethod => $this->sortClassMethod($node),
            $node instanceof Class_ => $this->sortClass($node),
            $node instanceof EnumCase => $this->sortEnumCase($node),
            $node instanceof Enum_ => $this->sortEnum($node),
            //            $node instanceof Expr => $this->sortExpr($node),
            //            $node instanceof Expression => $this->sortExpression($node),
            $node instanceof FuncCall => $this->sortFuncCall($node),
            //            $node instanceof While_ => $this->sortWhile($node),
            $node instanceof Function_ => $this->sortFunction($node),
            //            $node instanceof Identical => $this->sortIdentical($node),
            //            $node instanceof If_ => $this->sortIf($node),
            $node instanceof Instanceof_ => $this->sortInstanceof($node),
            $node instanceof Interface_ => $this->sortInterface($node),
            $node instanceof MatchArm => $this->sortMatchArm($node),
            $node instanceof Match_ => $this->sortMatch($node),
            $node instanceof MethodCall => $this->sortMethodCall($node),
            //            $node instanceof Name => $this->sortName($node),
            //            $node instanceof Nop => $this->sortNop($node),
            $node instanceof Param => $this->sortParam($node),
            $node instanceof Property => $this->sortProperty($node),
            $node instanceof PropertyFetch => $this->sortPropertyFetch($node),
            //            $node instanceof Return_ => $this->sortReturn($node),
            //            $node instanceof String_ => $this->sortString($node),
            $node instanceof Ternary => $this->sortTernary($node),
            $node instanceof TraitUse => $this->sortTraitUse($node),
            $node instanceof Trait_ => $this->sortTrait($node),
            $node instanceof Use_ => $this->sortUse($node),
            $node instanceof Static_ => $this->sortStatic($node),
            //            $node instanceof ClassLike => $this->sortClassLike($node),
            $node instanceof ClassConstFetch => $this->sortClassConstFetch($node),
            $node instanceof GroupUse => $this->sortGroupUse($node),
            $node instanceof AttributeGroup => $this->sortAttributeGroup($node),
            $node instanceof Namespace_ => $this->sortNamespace($node),
            $node instanceof UseItem => $this->sortUseItem($node),
            default => $node,
        };
    }

    public function sortNop(Nop $nop): Nop
    {
        return $nop;
    }

    public function sortParam(Param $param): Param
    {
        usort($param->attrGroups, fn (Node $left, Node $right): int => $this->compare($left, $right));

        return $param;
    }

    public function sortProperty(Property $property): Property
    {
        usort($property->attrGroups, fn (Node $left, Node $right): int => $this->compare($left, $right));
        usort($property->props, fn (Node $left, Node $right): int => $this->compare($left, $right));

        return $property;
    }

    public function sortPropertyFetch(PropertyFetch $propertyFetch): PropertyFetch
    {
        return $propertyFetch;
    }

    public function sortReturn(Return_ $return): Return_
    {
        return $return;
    }

    public function sortStatic(Static_ $static): Static_
    {
        usort($static->vars, fn (Node $left, Node $right): int => $this->compare($left, $right));

        return $static;
    }

    public function sortString(String_ $string): String_
    {
        return $string;
    }

    public function sortTernary(Ternary $ternary): Ternary
    {
        return $ternary;
    }

    public function sortTrait(Trait_ $trait): Trait_
    {
        usort($trait->attrGroups, fn (Node $left, Node $right): int => $this->compare($left, $right));
        usort($trait->stmts, fn (Node $left, Node $right): int => $this->compare($left, $right));

        return $trait;
    }

    public function sortTraitUse(TraitUse $traitUse): TraitUse
    {
        usort($traitUse->adaptations, fn (Node $left, Node $right): int => $this->compare($left, $right));
        usort($traitUse->traits, fn (Node $left, Node $right): int => $this->compare($left, $right));

        return $traitUse;
    }

    public function sortTryCatch(TryCatch $tryCatch): TryCatch
    {
        usort($tryCatch->stmts, fn (Node $left, Node $right): int => $this->compare($left, $right));
        usort($tryCatch->catches, fn (Node $left, Node $right): int => $this->compare($left, $right));

        return $tryCatch;
    }

    public function sortUse(Use_ $use): Use_
    {
        usort($use->uses, fn (UseItem $left, UseItem $right): int => $this->compare($left, $right));

        return $use;
    }

    public function sortUseItem(UseItem $useItem): UseItem
    {
        return $useItem;
    }

    private function compare(Node $left, Node $right): int
    {
        return $this->nodeComparer->compare($left, $right);
    }

    private function sortArg(Arg $arg): Arg
    {
        return $arg;
    }

    private function sortArray(Array_ $array): Array_
    {
        return $array;
    }

    private function sortArrayDimFetch(ArrayDimFetch $arrayDimFetch): ArrayDimFetch
    {
        return $arrayDimFetch;
    }

    private function sortArrayItem(ArrayItem $arrayItem): ArrayItem
    {
        return $arrayItem;
    }

    private function sortAssign(Assign $assign): Assign
    {
        return $assign;
    }

    private function sortAssignOpConcat(Concat $concat): Concat
    {
        return $concat;
    }

    private function sortAssignOpPlus(Plus $plus): Plus
    {
        return $plus;
    }

    private function sortAttribute(Attribute $attribute): Attribute
    {
        return $attribute;
    }

    private function sortBinaryOpBooleanOr(BooleanOr $booleanOr): BooleanOr
    {
        return $booleanOr;
    }

    private function sortBinaryOpConcat(PhpParserConcat $phpParserConcat): PhpParserConcat
    {
        return $phpParserConcat;
    }

    private function sortBinaryOpEqual(Equal $equal): Equal
    {
        return $equal;
    }

    private function sortBinaryOpGreater(Greater $greater): Greater
    {
        return $greater;
    }

    private function sortBinaryOpGreaterOrEqual(GreaterOrEqual $greaterOrEqual): GreaterOrEqual
    {
        return $greaterOrEqual;
    }

    private function sortBinaryOpMinus(Minus $minus): Minus
    {
        return $minus;
    }

    private function sortBinaryOpNotIdentical(NotIdentical $notIdentical): NotIdentical
    {
        return $notIdentical;
    }

    private function sortBinaryOpPlus(Plus $plus): Plus
    {
        return $plus;
    }

    private function sortBinaryOpSmaller(Smaller $smaller): Smaller
    {
        return $smaller;
    }

    private function sortBinaryOpSmallerOrEqual(SmallerOrEqual $smallerOrEqual): SmallerOrEqual
    {
        return $smallerOrEqual;
    }

    private function sortBooleanAnd(BooleanAnd $booleanAnd): BooleanAnd
    {
        return $booleanAnd;
    }

    private function sortBooleanNot(BooleanNot $booleanNot): BooleanNot
    {
        return $booleanNot;
    }

    private function sortBreak(Break_ $break): Break_
    {
        return $break;
    }

    private function sortCastArray(Array_ $array): Array_
    {
        return $array;
    }

    private function sortCastInt(Int_ $int): Int_
    {
        return $int;
    }

    private function sortCastString(String_ $string): String_
    {
        return $string;
    }

    private function sortCatch(Catch_ $catch): Catch_
    {
        return $catch;
    }

    private function sortClone(Clone_ $clone): Clone_
    {
        return $clone;
    }

    private function sortClosure(Closure $node): Closure
    {
        return $node;
    }

    private function sortClosureUse(ClosureUse $node): ClosureUse
    {
        return $node;
    }

    private function sortCoalesce(Coalesce $coalesce): Coalesce
    {
        return $coalesce;
    }

    private function sortConst(Const_ $const): Const_
    {
        return $const;
    }

    private function sortConstFetch(ConstFetch $constFetch): ConstFetch
    {
        return $constFetch;
    }

    private function sortContinue(Continue_ $continue): Continue_
    {
        return $continue;
    }

    private function sortDeclare(Declare_ $declare): Declare_
    {
        return $declare;
    }

    private function sortDeclareDeclare(DeclareDeclare $declareDeclare): DeclareDeclare
    {
        return $declareDeclare;
    }

    private function sortDir(Dir $dir): Dir
    {
        return $dir;
    }

    private function sortEcho(Echo_ $echo): Echo_
    {
        return $echo;
    }

    private function sortElse(Else_ $else): Else_
    {
        return $else;
    }

    private function sortElseIf(ElseIf_ $elseIf): ElseIf_
    {
        return $elseIf;
    }

    private function sortEncapsed(Encapsed $encapsed): Encapsed
    {
        return $encapsed;
    }

    private function sortErrorSuppress(ErrorSuppress $errorSuppress): ErrorSuppress
    {
        return $errorSuppress;
    }

    private function sortExit(Exit_ $exit): Exit_
    {
        return $exit;
    }

    private function sortExpr(Expr $expr): Expr
    {
        return $expr;
    }

    private function sortFile(File $file): File
    {
        return $file;
    }

    private function sortFor(For_ $for): For_
    {
        return $for;
    }

    private function sortIdentifier(Identifier $identifier): Identifier
    {
        return $identifier;
    }

    private function sortInclude(Include_ $include): Include_
    {
        return $include;
    }

    private function sortInlineHTML(InlineHTML $inlineHTML): InlineHTML
    {
        return $inlineHTML;
    }

    private function sortIsset(Isset_ $isset): Isset_
    {
        return $isset;
    }

    private function sortLNumber(LNumber $lNumber): LNumber
    {
        return $lNumber;
    }

    private function sortNew(New_ $new): New_
    {
        return $new;
    }

    private function sortNullableType(NullableType $nullableType): NullableType
    {
        return $nullableType;
    }

    private function sortPostDec(PostDec $postDec): PostDec
    {
        return $postDec;
    }

    private function sortPostInc(PostInc $postInc): PostInc
    {
        return $postInc;
    }

    private function sortPropertyItem(PropertyItem $propertyItem): PropertyItem
    {
        return $propertyItem;
    }

    private function sortStaticCall(StaticCall $staticCall): StaticCall
    {
        return $staticCall;
    }

    private function sortThrow(Throw_ $throw): Throw_
    {
        return $throw;
    }

    private function sortUnaryMinus(UnaryMinus $unaryMinus): UnaryMinus
    {
        return $unaryMinus;
    }

    private function sortUnset(Unset_ $unset): Unset_
    {
        return $unset;
    }

    private function sortVariable(Variable $variable): Variable
    {
        return $variable;
    }

    private function sortWhile(While_ $while): While_
    {
        return $while;
    }
}
