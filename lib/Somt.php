<?php
/**
 * Who ?: Chaz6chez
 * How !: 250220719@qq.com
 * Where: http://chaz6chez.top
 * Time : 2020/10/23|22:51
 * What : Creating Fucking Bug For Every Code
 */
namespace Chaz;

class Somt {
    private static $_clearing_scale = 2;
    private static $_clearing_overflow = 1;

    public static function ClearingSetScale(int $scale){
        self::$_clearing_scale = $scale;
    }

    public static function ClearingGetScale() : int {
        return self::$_clearing_scale;
    }

    public static function ClearingSetOverflow(int $overflow){
        $overflow = ($overflow < 1) ? 1 : $overflow;
        self::$_clearing_overflow = $overflow;
    }

    public static function ClearingGetOverflow() : int {
        return self::$_clearing_overflow;
    }

    public static function ClearingAdd($left_operand, $right_operand){
        return (string)round(
            (float)bcadd(
                (string)$left_operand,
                (string)$right_operand,
                self::ClearingGetScale() + self::ClearingGetOverflow()
            ),
            self::ClearingGetScale(),
            PHP_ROUND_HALF_EVEN
        );
    }

    public static function ClearingSub($left_operand, $right_operand){
        return (string)round(
            (float)bcsub(
                (string)$left_operand,
                (string)$right_operand,
                self::ClearingGetScale() + self::ClearingGetOverflow()
            ),
            self::ClearingGetScale(),
            PHP_ROUND_HALF_EVEN
        );
    }

    public static function ClearingComp($left_operand, $right_operand){
        return bccomp(
            (string)$left_operand,
            (string)$right_operand,
            self::ClearingGetScale() + self::ClearingGetOverflow()
        );
    }

    public static function ClearingDiv($left_operand, $right_operand){
        return (string)round(
            (float)bcdiv(
                (string)$left_operand,
                (string)$right_operand,
                self::ClearingGetScale() + self::ClearingGetOverflow()
            ),
            self::ClearingGetScale(),
            PHP_ROUND_HALF_EVEN
        );
    }

    public static function ClearingMul($left_operand, $right_operand){
        return (string)round(
            (float)bcmul(
                (string)$left_operand,
                (string)$right_operand,
                self::ClearingGetScale() + self::ClearingGetOverflow()
            ),
            self::ClearingGetScale(),
            PHP_ROUND_HALF_EVEN
        );
    }
}