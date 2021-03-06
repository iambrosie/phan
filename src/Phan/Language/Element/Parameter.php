<?php declare(strict_types=1);
namespace Phan\Language\Element;

use \Phan\CodeBase;
use \Phan\Debug;
use \Phan\Language\Context;
use \Phan\Language\Type\NullType;
use \Phan\Language\UnionType;
use \Phan\Log;
use \ast\Node;

class Parameter extends Variable {

    /**
     * @var \mixed
     * The default value for a parameter
     */
    private $default_value = null;

    /**
     * @var UnionType
     * The type of the default value if any
     */
    private $default_value_type = null;

    /**
     * @param \phan\Context $context
     * The context in which the structural element lives
     *
     * @param string $name,
     * The name of the typed structural element
     *
     * @param UnionType $type,
     * A '|' delimited set of types satisfyped by this
     * typed structural element.
     *
     * @param int $flags,
     * The flags property contains node specific flags. It is
     * always defined, but for most nodes it is always zero.
     * ast\kind_uses_flags() can be used to determine whether
     * a certain kind has a meaningful flags value.
     */
    public function __construct(
        Context $context,
        string $name,
        UnionType $type,
        int $flags
    ) {
        parent::__construct(
            $context,
            $name,
            $type,
            $flags
        );
    }

    /**
     * After a clone is called on this object, clone our
     * deep objects.
     *
     * @return null
     */
    public function __clone() {
        parent::__clone();
        $this->default_value_type = $this->default_value_type
            ? clone($this->default_value_type)
            : $this->default_value_type;
    }

    public function setUnionType(UnionType $type) {
        parent::setUnionType($type);
    }

    /**
     * @return \mixed
     * The default value for the parameter if one
     * exists
     */
    public function getDefaultValue() {
        return $this->default_value;
    }

    /**
     * @param UnionType $type
     * The type of the default value for this parameter
     *
     * @return null
     */
    public function setDefaultValue(
        $default_value,
        UnionType $type
    ) {
        $this->default_value = $default_value;
        $this->default_value_type = $type;
    }

    /**
     * @return bool
     * True if this parameter has a type for its
     * default value
     */
    public function hasDefaultValue() : bool {
        return !empty($this->default_value_type);
    }

    /**
     * @return UnionType
     * The type of the default value for this parameter
     * if it exists
     */
    public function getDefaultValueType() : UnionType {
        return $this->default_value_type;
    }

    /**
     * @return Parameter[]
     * A list of parameters from an AST node.
     *
     * @see \Phan\Deprecated\Pass1::node_paramlist
     * Formerly `function node_paramlist`
     */
    public static function listFromNode(
        Context $context,
        CodeBase $code_base,
        Node $node
    ) : array {
        assert($node instanceof Node, "node was not an \\ast\\Node");

        $parameter_list = [];
        $is_optional_seen = false;
        foreach ($node->children ?? [] as $i => $child_node) {
            $parameter =
                Parameter::fromNode($context, $code_base, $child_node);

            if (!$parameter->isOptional() && $is_optional_seen) {
                Log::err(
                    Log::EPARAM,
                    "required arg follows optional",
                    $context->getFile(),
                    $node->lineno
                );
            } else if ($parameter->isOptional()) {
                $is_optional_seen = true;
            }

            $parameter_list[] = $parameter;
        }

        return $parameter_list;
    }

    /**
     * @return Parameter
     * A parameter built from a node
     *
     * @see \Phan\Deprecated\Pass1::node_param
     * Formerly `function node_param`
     */
    public static function fromNode(
        Context $context,
        CodeBase $code_base,
        Node $node
    ) : Parameter {

        assert($node instanceof Node, "node was not an \\ast\\Node");

        // Get the type of the parameter
        $type = UnionType::fromSimpleNode(
            $context,
            $node->children['type']
        );

        $comment =
            Comment::fromStringInContext(
                $node->docComment ?? '',
                $context
            );

        // Create the skeleton parameter from what we know so far
        $parameter = new Parameter(
            $context,
            (string)$node->children['name'],
            $type,
            $node->flags
        );

        // If there is a default value, store it and its type
        if (($default_node = $node->children['default']) !== null) {

            // We can't figure out default values during the
            // parsing phase, unfortunately
            if (!($default_node instanceof Node)
                || $default_node->kind == \ast\AST_CONST
                || $default_node->kind == \ast\AST_UNARY_OP
                || $default_node->kind == \ast\AST_ARRAY
            ) {
                // Set the default value
                $parameter->setDefaultValue(
                    $node->children['default'],
                    UnionType::fromNode(
                        $context,
                        $code_base,
                        $node->children['default']
                    )
                );
            } else {
                // Nodes here may be of type \ast\AST_CLASS_CONST
                // which we can't figure out during the first
                // parsing pass
                $parameter->setDefaultValue(
                    null,
                    NullType::instance()->asUnionType()
                );
            }

        }

        return $parameter;
    }

    /**
     * @return bool
     * True if this is an optional parameter
     */
    public function isOptional() : bool {
        return $this->hasDefaultValue();
    }

    /**
     * @return bool
     * True if this is a required parameter
     */
    public function isRequired() : bool {
        return !$this->isOptional();
    }

    /**
     * @return bool
     * True if this parameter is variadic, i.e. can
     * take an unlimited list of parameters and express
     * them as an array.
     */
    public function isVariadic() : bool {
        return (bool)(
            $this->getFlags() & \ast\flags\PARAM_VARIADIC
        );
    }

    /**
     * @return bool
     * True if this parameter is pass-by-reference
     * i.e. prefixed with '&'.
     */
    public function isPassByReference() : bool {
        return (bool)(
            $this->getFlags() & \ast\flags\PARAM_REF
        );
    }

    public function __toString() : string {
        $string = '';

        if (!$this->getUnionType()->isEmpty()) {
            $string .= (string)$this->getUnionType() . ' ';
        }

        if ($this->isPassByReference()) {
            $string .= '&';
        }

        $string .= "\${$this->getName()}";

        if ($this->isVariadic()) {
            $string .= ' ...';
        }

        if ($this->isOptional()) {
            $string .= ' = null';
        }

        return $string;
    }

}
