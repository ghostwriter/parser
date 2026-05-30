<?php

declare(strict_types=1);

namespace Ghostwriter\Parser\Node\Comparator;

use Ghostwriter\Parser\Printer;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\MatchArm;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Continue_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\EnumCase;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Static_;
use PhpParser\Node\Stmt\StaticVar;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\Node\Stmt\TraitUseAdaptation\Alias;
use PhpParser\Node\Stmt\TryCatch;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\Node\UseItem;

use function dd;
use function strnatcasecmp;

final readonly class NodeComparator
{
    private const int AFTER = 1;

    private const int BEFORE = -1;

    private const int SAME = 0;

    public function __construct(
        private Printer $printer
    ) {}

    public function compare(Node $left, Node $right): int
    {
        return match (true) {
            //            $left instanceof ElseIf_ => match (true) {
            //                $right instanceof ElseIf_ => $this->compareElseIf($left, $right),
            //                default => dd([$left::class, $right::class, __LINE__, __FUNCTION__]),
            //            },
            //            $left instanceof Node\StaticVar => match (true) {
            //                $right instanceof Node\StaticVar => $this->compareStaticVar($left, $right),
            //                default => dd([$left::class, $right::class, __LINE__, __FUNCTION__]),
            //            },
            //            $left instanceof Arg => match (true) {
            //                $right instanceof Arg => $this->compareArg($left, $right),
            //                default => dd([$left::class, $right::class, __LINE__, __FUNCTION__]),
            //            },
            //            //            $left instanceof Expr => match (true) {
            //            // //                $right instanceof Expr => $this->compareExpr($left, $right),
            //            //                default => die(var_dump([$left::class, $right::class, __LINE__, __FUNCTION__)),
            //            //            },
            //            $left instanceof Foreach_ => match (true) {
            //                $right instanceof Expression, $right instanceof Return_ => 0,
            //                $right instanceof Foreach_ => $this->compareForeach($left, $right),
            //                default => dd([$left::class, $right::class, __LINE__, __FUNCTION__]),
            //            },
            //            $left instanceof TryCatch => match (true) {
            //                $right instanceof If_ => 0,
            //                $right instanceof TryCatch => $this->compareTryCatch($left, $right),
            //                default => dd([$left::class, $right::class, __LINE__, __FUNCTION__]),
            //            },
            //            $left instanceof AttributeGroup => match (true) {
            //                $right instanceof AttributeGroup => $this->compareAttributeGroup($left, $right),
            //                default => dd([$left::class, $right::class, __LINE__, __FUNCTION__]),
            //            },
            //            $left instanceof ClassConst => match (true) {
            //                $right instanceof ClassConst => $this->compareClassConst($left, $right),
            //                $right instanceof ClassMethod => self::BEFORE,
            //                $right instanceof Property => self::BEFORE,
            //                $right instanceof TraitUse => self::AFTER,
            //                $right instanceof Nop => self::SAME,
            //                default => dd([$left::class, $right::class, __LINE__, __FUNCTION__]),
            //            },
            //            $left instanceof ClassConstFetch => match (true) {
            //                $right instanceof ClassConstFetch => $this->compareClassConstFetch($left, $right),
            //                $right instanceof Ternary => 0,
            //                default => dd([$left::class, $right::class, __LINE__, __FUNCTION__]),
            //            },
            //            $left instanceof ClassMethod => match (true) {
            //                $right instanceof ClassConst => self::AFTER,
            //                $right instanceof EnumCase => self::AFTER,
            //                $right instanceof TraitUse => self::AFTER,
            //                $right instanceof Property => self::AFTER,
            //                $right instanceof ClassMethod => $this->compareClassMethod($left, $right),
            //                default => dd([$left::class, $right::class, __LINE__, __FUNCTION__]),
            //            },
            //            $left instanceof Class_ => match (true) {
            //                $right instanceof Class_ => $this->compareClass($left, $right),
            //                $right instanceof Interface_ => self::AFTER,
            //                $right instanceof Enum_ => self::AFTER,
            //                $right instanceof Trait_ => self::BEFORE,
            //                $right instanceof Use_ => self::AFTER,
            //                $right instanceof Expression, $right instanceof Function_, $right instanceof If_, $right instanceof Nop => 0,
            //                default => dd([$left::class, $right::class, __LINE__, __FUNCTION__]),
            //            },
            //            $left instanceof EnumCase => match (true) {
            //                $right instanceof ClassMethod => self::BEFORE,
            //                $right instanceof EnumCase => $this->compareEnumCase($left, $right),
            //                default => dd([$left::class, $right::class, __LINE__, __FUNCTION__]),
            //            },
            //            $left instanceof Enum_ => match (true) {
            //                $right instanceof Class_ => self::BEFORE,
            //                $right instanceof Use_ => self::AFTER,
            //                $right instanceof Trait_ => self::BEFORE,
            //                $right instanceof Interface_ => self::BEFORE,
            //                $right instanceof Enum_ => $this->compareEnum($left, $right),
            //                default => dd([$left::class, $right::class, __LINE__, __FUNCTION__]),
            //            },
            //            $left instanceof Expression => match (true) {
            //                $right instanceof Continue_, $right instanceof For_, $right instanceof Foreach_, $right instanceof TryCatch, $right instanceof Class_, $right instanceof Trait_, $right instanceof If_, $right instanceof Nop, $right instanceof Return_, $right instanceof Use_ => 0,
            //                $right instanceof Expression => $this->compareExpression($left, $right),
            //                default => dd([$left::class, $right::class, __LINE__, __FUNCTION__]),
            //            },
            //            $left instanceof FuncCall => match (true) {
            //                $right instanceof FuncCall => $this->compareFuncCall($left, $right),
            //                default => dd([$left::class, $right::class, __LINE__, __FUNCTION__]),
            //            },
            //            $left instanceof UseItem => match (true) {
            //                $right instanceof UseItem => $this->compareUseItem($left, $right),
            //                default => dd([$left::class, $right::class, __LINE__, __FUNCTION__]),
            //            },
            //            $left instanceof Function_ => match (true) {
            //                $right instanceof Class_, $right instanceof If_, $right instanceof Use_ => 0,
            //                $right instanceof Function_ => $this->compareFunction($left, $right),
            //                default => dd([$left::class, $right::class, __LINE__, __FUNCTION__]),
            //            },
            //            $left instanceof Identical, $left instanceof String_, $left instanceof Return_, $left instanceof PropertyFetch, $left instanceof Ternary, $left instanceof Param => 0,
            //            $left instanceof If_ => match (true) {
            //                $right instanceof Throw_, $right instanceof Return_, $right instanceof Class_, $right instanceof Expression, $right instanceof Function_, $right instanceof If_, $right instanceof Nop, $right instanceof Use_ => 0,
            //                default => dd([$left::class, $right::class, __LINE__, __FUNCTION__]),
            //            },
            //            $left instanceof Interface_ => match (true) {
            //                $right instanceof Class_ => self::AFTER,
            //                $right instanceof Enum_ => self::AFTER,
            //                $right instanceof Trait_ => self::BEFORE,
            //                $right instanceof Use_ => self::BEFORE,
            //                $right instanceof Interface_ => $this->compareInterface($left, $right),
            //                default => dd([$left::class, $right::class, __LINE__, __FUNCTION__]),
            //            },
            //            $left instanceof Static_ => match (true) {
            //                $right instanceof If_, $right instanceof Expression => 0,
            //                $right instanceof Static_ => $this->compareStatic($left, $right),
            //                default => dd([$left::class, $right::class, __LINE__, __FUNCTION__]),
            //            },
            //            $left instanceof Instanceof_ => match (true) {
            //                $right instanceof Identical => 0,
            //                $right instanceof Instanceof_ => $this->compareInstanceof($left, $right),
            //                default => dd([$left::class, $right::class, __LINE__, __FUNCTION__]),
            //            },
            //            $left instanceof Alias => match (true) {
            //                $right instanceof Alias => $this->compareTraitUseAdaptationAlias($left, $right),
            //                default => dd([$left::class, $right::class, __LINE__, __FUNCTION__]),
            //            },
            //            $left instanceof MatchArm => match (true) {
            //                $right instanceof MatchArm => $this->compareMatchArm($left, $right),
            //                default => dd([$left::class, $right::class, __LINE__, __FUNCTION__]),
            //            },
            //            $left instanceof Name => match (true) {
            //                $right instanceof Name => $this->compareName($left, $right),
            //                default => dd([$left::class, $right::class, __LINE__, __FUNCTION__]),
            //            },
            //            $left instanceof GroupUse => match (true) {
            //                $right instanceof GroupUse => $this->compareGroupUse($left, $right),
            //                $right instanceof Class_ => self::BEFORE,
            //                $right instanceof Use_ => self::AFTER,
            //                $right instanceof Enum_ => self::BEFORE,
            //                default => dd([$left::class, $right::class, __LINE__, __FUNCTION__]),
            //            },
            //            $left instanceof Property => match (true) {
            //                $right instanceof ClassConst => self::AFTER,
            //                $right instanceof ClassMethod => self::BEFORE,
            //                $right instanceof TraitUse => self::AFTER,
            //                $right instanceof Property => $this->compareProperty($left, $right),
            //                default => dd([$left::class, $right::class, __LINE__, __FUNCTION__]),
            //            },
            //            $left instanceof TraitUse => match (true) {
            //                $right instanceof ClassConst, $right instanceof ClassMethod, $right instanceof Property => self::BEFORE,
            //                $right instanceof TraitUse => $this->compareTraitUse($left, $right),
            //                default => dd([$left::class, $right::class, __LINE__, __FUNCTION__]),
            //            },
            //            $left instanceof Trait_ => match (true) {
            //                $right instanceof Use_ => self::AFTER,
            //                $right instanceof Class_ => self::AFTER,
            //                $right instanceof Enum_ => self::BEFORE,
            //                $right instanceof Trait_ => $this->compareTrait($left, $right),
            //                default => dd([$left::class, $right::class, __LINE__, __FUNCTION__]),
            //            },
            $left instanceof Use_ => match (true) {
                //                $right instanceof GroupUse => self::BEFORE,
                $right instanceof Class_,
                // $right instanceof Enum_, $right instanceof Interface_, $right instanceof Trait_
                => self::BEFORE,
                $right instanceof Expression,
                //                    $right instanceof Function_,
                //                    $right instanceof If_,
                //                    $right instanceof Nop
                => self::SAME,
                $right instanceof Use_ => $this->compareUse($left, $right),
                default => dd([$left::class, $right::class, __LINE__, __FUNCTION__]),
            },
            //            $left instanceof Nop => match (true) {
            //                $right instanceof ClassConst, $right instanceof Expression, $right instanceof Class_, $right instanceof If_, $right instanceof Use_ => 0,
            //                default => dd([$left::class, $right::class, __LINE__, __FUNCTION__]),
            //            },
            default => dd([$left::class, $right::class, __LINE__, __FUNCTION__]),
        };
    }

    public function compareAttributeGroup(AttributeGroup $left, AttributeGroup $right): int
    {
        return $this->compareNode($left, $right);
    }

    public function compareClass(Class_ $left, Class_ $right): int
    {
        return $this->compareName($left, $right);
    }

    public function compareClassConst(ClassConst $left, ClassConst $right): int
    {
        return $this->compareNode($left, $right);
        //        return $this->getName($left) <=> $this->getName($right);
    }

    public function compareClassConstFetch(ClassConstFetch $left, ClassConstFetch $right): int
    {
        return $this->compareNode($left, $right);
    }

    public function compareClassLike(ClassLike $left, ClassLike $right): int
    {
        return $this->compareName($left, $right);

        //        $this->sort($left);
        return strnatcasecmp($this->getName($left), $this->getName($right));
    }

    public function compareClassMethod(ClassMethod $left, ClassMethod $right): int
    {
        return $this->getName($left) <=> $this->getName($right);
    }

    public function compareEnum(Enum_ $left, Enum_ $right): int
    {
        return $this->compareName($left, $right);
    }

    public function compareEnumCase(EnumCase $left, EnumCase $right): int
    {
        return $this->getName($left) <=> $this->getName($right);

        //        die(var_dump([[
        //            $this->getName($left) => $this->getName($right),
        //        ], strnatcasecmp($this->getName($left), $this->getName($right)), $this->getName($left) <=> $this->getName(
        //            $right
        //        ));
        return $this->compareNode($left, $right);
    }

    public function compareExpr(Expr $left, Expr $right): int
    {
        return strnatcasecmp(
            (string) $this->printer->prettyPrintExpr($left),
            (string) $this->printer->prettyPrintExpr($right)
        );
    }

    public function compareExpression(Expression $left, Expression $right): int
    {
        return $this->compareNode($left, $right);
    }

    public function compareForeach(Foreach_ $left, Foreach_ $right): int
    {
        return $this->compareNode($left, $right);
    }

    public function compareFuncCall(FuncCall $left, FuncCall $right): int
    {
        return $this->compareNode($left, $right);
        // $this->compareNode($left, $right);
    }

    public function compareFunction(Function_ $left, Function_ $right): int
    {
        return $this->compareName($left, $right);
    }

    public function compareGroupUse(GroupUse $left, GroupUse $right): int
    {
        return $this->compareNode($left, $right);

        return strnatcasecmp(
            (string) $this->printer->prettyPrint([$this->sortGroupUse($left)]),
            (string) $this->printer->prettyPrint([$this->sortGroupUse($right)])
        );
    }

    public function compareIdentical(Identical $left, Identical $right): int
    {
        return $this->compareNode($left, $right);
        // $this->compareNode($left, $right);
    }

    public function compareIf(If_ $left, If_ $right): int
    {
        return $this->compareNode($left, $right);
    }

    public function compareInstanceof(Instanceof_ $left, Instanceof_ $right): int
    {
        return $this->compareNode($left, $right);
        //        $this->compareNode($left, $right);
    }

    public function compareInterface(Interface_ $left, Interface_ $right): int
    {
        return $this->compareName($left, $right);
    }

    public function compareMatchArm(MatchArm $left, MatchArm $right): int
    {
        return $this->compareNode($left, $right);
    }

    public function compareName(Node $left, Node $right): int
    {
        return strnatcasecmp($this->getName($left), $this->getName($right));
    }

    public function compareNode(Node $left, Node $right): int
    {
        return strnatcasecmp($this->printer->prettyPrint([$left]), $this->printer->prettyPrint([$right]));
    }

    public function compareProperty(Property $left, Property $right): int
    {
        return $this->compareName($left, $right);
    }

    public function compareStatic(Static_ $left, Static_ $right): int
    {
        return $this->compareNode($left, $right);
    }

    public function compareTrait(Trait_ $left, Trait_ $right): int
    {
        return $this->compareNode($left, $right);
    }

    public function compareTraitUse(TraitUse $left, TraitUse $right): int
    {
        return $this->compareNode($left, $right);
    }

    public function compareTryCatch(TryCatch $left, TryCatch $right): int
    {
        return $this->compareNode($left, $right);
    }

    public function compareUse(Use_ $left, Use_ $right): int
    {
        return match ($left->type) {
            default => dd([$left::class, $right::class, __LINE__, __FUNCTION__]),
            Use_::TYPE_CONSTANT => match ($right->type) {
                default => dd([$left::class, $right::class, __LINE__, __FUNCTION__]),
                Use_::TYPE_CONSTANT => $this->compareNode($left, $right),
                Use_::TYPE_FUNCTION => self::BEFORE,
                Use_::TYPE_NORMAL => self::AFTER,
            },
            Use_::TYPE_FUNCTION => match ($right->type) {
                default => dd([$left::class, $right::class, __LINE__, __FUNCTION__]),
                Use_::TYPE_CONSTANT => self::AFTER,
                Use_::TYPE_NORMAL => self::AFTER,
                Use_::TYPE_FUNCTION => $this->compareNode($left, $right),
            },
            Use_::TYPE_NORMAL => match ($right->type) {
                default => dd([$left::class, $right::class, __LINE__, __FUNCTION__]),
                Use_::TYPE_CONSTANT => self::BEFORE,
                Use_::TYPE_FUNCTION => self::BEFORE,
                Use_::TYPE_NORMAL => $this->compareNode($left, $right),
            },
        };

        //        return $this->compareNode($this->sort($left), $this->sort($right));
    }

    private function compareArg(Arg $left, Arg $right): int
    {
        return $this->compareNode($left, $right);
    }

    private function compareElseIf(ElseIf_ $left, ElseIf_ $right): int
    {
        return $this->compareNode($left, $right);
    }

    private function compareStaticVar(StaticVar $left, StaticVar $right): int
    {
        return $this->compareNode($left, $right);
    }

    private function compareTraitUseAdaptationAlias(Alias $left, Alias $right): int
    {
        return $this->compareNode($left, $right);
    }

    //    private function compareElseIf(ElseIf_ $left, ElseIf_ $right): int
    //    {
    //        return $this->compareNode($left, $right);
    //    }
    //
    //    private function compareStaticVar(\PhpParser\Node\StaticVar $left, \PhpParser\Node\StaticVar $right): int
    //    {
    // //        return $this->getName($left) <=> $this->getName($right);
    //        return $this->compareNode($left, $right);
    //    }

    private function compareUseItem(UseItem $left, UseItem $right): int
    {
        return $this->compareNode($left, $right);
    }

    private function compareUseUse(UseUse $left, UseUse $right): int
    {
        return $this->compareNode($left, $right);
    }
}
