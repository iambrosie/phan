<?php declare(strict_types=1);
namespace Phan;

use \ast\Node;

/**
 * Debug utilities
 */
class Debug {

    /**
     * @param string|Node|null $node
     * An AST node
     *
     * Print an AST node
     *
     * @return null
     */
    public static function printNode($node) {
        print self::nodeToString($node);
    }

    /**
     * @param string|Node|null $node
     * An AST node
     *
     * @param int $indent
     * The indentation level for the string
     *
     * @return string
     * A string representation of an AST node
     */
    public static function nodeToString(
        $node,
        int $indent = 0
    ) : string {
        $string = str_repeat("\t", $indent);

        if (is_string($node)) {
            return $string . $node . "\n";
        }

        if (!$node) {
            return $string . 'null' . "\n";
        }

        if (!is_object($node)) {
            return $string . $node . "\n";
        }

        $string .= \ast\get_kind_name($node->kind);

        $string .= ' ['
            . self::astFlagDescription($node->flags ?? 0)
            . ']';

        if (isset($node->lineno)) {
            $string .= ' #' . $node->lineno;
        }

        if (isset($node->endLineno)) {
            $string .= ':' . $node->endLineno;
        }

        $string .= "\n";

        foreach ($node->children ?? [] as $child_node) {
            $string .= self::nodeToString($child_node, $indent + 1);
        }

        return $string;
    }

    /**
     * @return string
     * Get a string representation of AST node flags such as
     * 'ASSIGN_DIV|TYPE_ARRAY'
     */
    public static function astFlagDescription(int $flag) : string {
        $flag_names = [];
        foreach (self::$AST_FLAG_ID_NAME_MAP as $id => $name) {
            if ($flag & $id) {
                $flag_names[] = $name;
            }
        }

        return implode('|', $flag_names);
    }

    private static $AST_FLAG_ID_NAME_MAP = [
        \ast\flags\ASSIGN_ADD => 'ASSIGN_ADD',
        \ast\flags\ASSIGN_BITWISE_AND => 'ASSIGN_BITWISE_AND',
        \ast\flags\ASSIGN_BITWISE_OR => 'ASSIGN_BITWISE_OR',
        \ast\flags\ASSIGN_BITWISE_XOR => 'ASSIGN_BITWISE_XOR',
        \ast\flags\ASSIGN_CONCAT => 'ASSIGN_CONCAT',
        \ast\flags\ASSIGN_DIV => 'ASSIGN_DIV',
        \ast\flags\ASSIGN_MOD => 'ASSIGN_MOD',
        \ast\flags\ASSIGN_MUL => 'ASSIGN_MUL',
        \ast\flags\ASSIGN_POW => 'ASSIGN_POW',
        \ast\flags\ASSIGN_SHIFT_LEFT => 'ASSIGN_SHIFT_LEFT',
        \ast\flags\ASSIGN_SHIFT_RIGHT => 'ASSIGN_SHIFT_RIGHT',
        \ast\flags\ASSIGN_SUB => 'ASSIGN_SUB',
        \ast\flags\BINARY_ADD => 'BINARY_ADD',
        \ast\flags\BINARY_BITWISE_AND => 'BINARY_BITWISE_AND',
        \ast\flags\BINARY_BITWISE_OR => 'BINARY_BITWISE_OR',
        \ast\flags\BINARY_BITWISE_XOR => 'BINARY_BITWISE_XOR',
        \ast\flags\BINARY_BOOL_XOR => 'BINARY_BOOL_XOR',
        \ast\flags\BINARY_CONCAT => 'BINARY_CONCAT',
        \ast\flags\BINARY_DIV => 'BINARY_DIV',
        \ast\flags\BINARY_IS_EQUAL => 'BINARY_IS_EQUAL',
        \ast\flags\BINARY_IS_IDENTICAL => 'BINARY_IS_IDENTICAL',
        \ast\flags\BINARY_IS_NOT_EQUAL => 'BINARY_IS_NOT_EQUAL',
        \ast\flags\BINARY_IS_NOT_IDENTICAL => 'BINARY_IS_NOT_IDENTICAL',
        \ast\flags\BINARY_IS_SMALLER => 'BINARY_IS_SMALLER',
        \ast\flags\BINARY_IS_SMALLER_OR_EQUAL => 'BINARY_IS_SMALLER_OR_EQUAL',
        \ast\flags\BINARY_MOD => 'BINARY_MOD',
        \ast\flags\BINARY_MUL => 'BINARY_MUL',
        \ast\flags\BINARY_POW => 'BINARY_POW',
        \ast\flags\BINARY_SHIFT_LEFT => 'BINARY_SHIFT_LEFT',
        \ast\flags\BINARY_SHIFT_RIGHT => 'BINARY_SHIFT_RIGHT',
        \ast\flags\BINARY_SPACESHIP => 'BINARY_SPACESHIP',
        \ast\flags\BINARY_SUB => 'BINARY_SUB',
        \ast\flags\CLASS_ABSTRACT => 'CLASS_ABSTRACT',
        \ast\flags\CLASS_FINAL => 'CLASS_FINAL',
        \ast\flags\CLASS_INTERFACE => 'CLASS_INTERFACE',
        \ast\flags\CLASS_TRAIT => 'CLASS_TRAIT',
        \ast\flags\MODIFIER_ABSTRACT => 'MODIFIER_ABSTRACT',
        \ast\flags\MODIFIER_FINAL => 'MODIFIER_FINAL',
        \ast\flags\MODIFIER_PRIVATE => 'MODIFIER_PRIVATE',
        \ast\flags\MODIFIER_PROTECTED => 'MODIFIER_PROTECTED',
        \ast\flags\MODIFIER_PUBLIC => 'MODIFIER_PUBLIC',
        \ast\flags\MODIFIER_STATIC => 'MODIFIER_STATIC',
        \ast\flags\NAME_FQ => 'NAME_FQ',
        \ast\flags\NAME_NOT_FQ => 'NAME_NOT_FQ',
        \ast\flags\NAME_RELATIVE => 'NAME_RELATIVE',
        \ast\flags\PARAM_REF => 'PARAM_REF',
        \ast\flags\PARAM_VARIADIC => 'PARAM_VARIADIC',
        \ast\flags\RETURNS_REF => 'RETURNS_REF',
        \ast\flags\TYPE_ARRAY => 'TYPE_ARRAY',
        \ast\flags\TYPE_BOOL => 'TYPE_BOOL',
        \ast\flags\TYPE_CALLABLE => 'TYPE_CALLABLE',
        \ast\flags\TYPE_DOUBLE => 'TYPE_DOUBLE',
        \ast\flags\TYPE_LONG => 'TYPE_LONG',
        \ast\flags\TYPE_NULL => 'TYPE_NULL',
        \ast\flags\TYPE_OBJECT => 'TYPE_OBJECT',
        \ast\flags\TYPE_STRING => 'TYPE_STRING',
        \ast\flags\UNARY_BITWISE_NOT => 'UNARY_BITWISE_NOT',
        \ast\flags\UNARY_BOOL_NOT => 'UNARY_BOOL_NOT',
    ];
}
