<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Yaml;

/**
 * Dumper dumps PHP variables to YAML strings.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Dumper
{
    /**
     * The amount of spaces to use for indentation of nested nodes.
     *
     * @var int
     */
    protected $indentation = 4;

    /**
     * Sets the indentation.
     *
     * @param int $num The amount of spaces to use for indentation of nested nodes.
     */
    public function setIndentation($num)
    {
        $this->indentation = (int) $num;
    }

    /**
     * Dumps a PHP value to YAML.
     *
     * @param mixed $input                  The PHP value
     * @param int   $inline                 The level where you switch to inline YAML
     * @param int   $indent                 The level of indentation (used internally)
     * @param bool  $exceptionOnInvalidType true if an exception must be thrown on invalid types (a PHP resource or object), false otherwise
     * @param bool  $objectSupport          true if object support is enabled, false otherwise
     *
     * @return string The YAML representation of the PHP value
     */
    public function dump($input, $inline = 0, $indent = 0, $exceptionOnInvalidType = false, $objectSupport = false)
    {
        return $this->commentedDump($input, [], $inline, $indent, $exceptionOnInvalidType, $objectSupport);
    }

    /**
     * Dumps a PHP value to a commented YAML structure.
     *
     * @param mixed $input                  The PHP value
     * @param mixed $comments               The comments that go with the php value
     * @param int   $inline                 The level where you switch to inline YAML
     * @param int   $indent                 The level of indentation (used internally)
     * @param bool  $exceptionOnInvalidType true if an exception must be thrown on invalid types (a PHP resource or object), false otherwise
     * @param bool  $objectSupport          true if object support is enabled, false otherwise
     *
     * @return string The YAML representation of the PHP value
     */
    public function commentedDump($input, $comments, $inline = 0, $indent = 0, $exceptionOnInvalidType = false, $objectSupport = false)
    {
        $output = '';
        $prefix = $indent ? str_repeat(' ', $indent) : '';

        // Handle top-level comments here. All other comments are inserted
        // below, inside the `foreach($input)` loop
        if (isset($comments['#'])) {
            $output .= $this->formatComment($comments['#'], $indent);
        }
        if ($inline <= 0 || !is_array($input) || empty($input)) {
            $output .= $prefix.Inline::dump($input, $exceptionOnInvalidType, $objectSupport);
        } else {
            $isAHash = array_keys($input) !== range(0, count($input) - 1);

            foreach ($input as $key => $value) {
                $willBeInlined = $inline - 1 <= 0 || !is_array($value) || empty($value);
                $subcomments = $this->subComments($comments, $key);
                $comment = isset($subcomments['#']) ? $subcomments['#'] : '';
                unset($subcomments['#']);

                $output .= sprintf('%s%s%s%s%s',
                    $this->formatComment($comment, $indent),
                    $prefix,
                    $isAHash ? Inline::dump($key, $exceptionOnInvalidType, $objectSupport).':' : '-',
                    $willBeInlined ? ' ' : "\n",
                    $this->commentedDump($value, $subcomments, $inline - 1, $willBeInlined ? 0 : $indent + $this->indentation, $exceptionOnInvalidType, $objectSupport)
                ).($willBeInlined ? "\n" : '');
            }
        }

        return $output;
    }

    /**
     * Convert a comment string into an appropriately-indented
     * block of text prefixed with the comment character ("#").
     *
     * @param string $commentString         The comment to format
     * @param int   $indent                 The level of indentation
     */
    protected function formatComment($commentString, $indent)
    {
        $output = '';
        if (strlen($commentString) > 0) {
            $prefix = $indent ? str_repeat(' ', $indent) : '';
            foreach (explode("\n", $commentString) as $commentLine) {
                $output .= "$prefix# $commentLine\n";
            }
        }
        return $output;
    }

    /**
     * Look up the subcomments in the specified comments array.
     * These are the comments that apply to the data at $key.
     *
     * @param mixed $comments       An associative array of comments,
     *                              or a singe comment string
     * @param $key                  The key of the data to retreive comments for
     */
    protected function subComments($comments, $key)
    {
        if (!isset($comments[$key])) {
            return [];
        }
        if (is_array($comments[$key])) {
            return $comments[$key];
        }
        return[ '#' => $comments[$key] ];
    }
}
