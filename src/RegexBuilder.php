<?php
/* ===========================================================================
 * Copyright 2018-2020 Zindex Software
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ============================================================================ */

namespace Opis\Routing;

use RuntimeException;

class RegexBuilder
{
    /** @var array */
    protected $options;

    /** @var array */
    protected $tokens = [];

    const CAPTURE_LEFT = 1;
    const CAPTURE_RIGHT = 2;
    const ALLOW_OPT_TRAIL = 4;

    const START_SYMBOL = 0;
    const END_SYMBOL = 1;
    const SEPARATOR_SYMBOL = 2;
    const OPT_SYMBOL = 3;
    const CAPTURE_MODE = 4;
    const REGEX_DELIMITER = 5;
    const REGEX_MODIFIER = 6;
    const DEFAULT_REGEX_EXP = 7;
    const ASSIGN_SYMBOL = 8;

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $options += [
            self::START_SYMBOL => '{',
            self::END_SYMBOL => '}',
            self::SEPARATOR_SYMBOL => '/',
            self::OPT_SYMBOL => '?',
            self::CAPTURE_MODE => self::CAPTURE_LEFT | self::ALLOW_OPT_TRAIL,
            self::REGEX_DELIMITER => '~',
            self::REGEX_MODIFIER => 'u',
            self::ASSIGN_SYMBOL => '=',
        ];

        if (!isset($options[self::DEFAULT_REGEX_EXP])) {
            $expr = preg_quote($options[self::SEPARATOR_SYMBOL], $options[self::REGEX_DELIMITER]);
            $options[self::DEFAULT_REGEX_EXP] = '[^' . $expr . ']+';
        }

        $this->options = $options;
    }

    /**
     * @param string $pattern
     * @param array $placeholders
     * @return string
     */
    public function getRegex(string $pattern, array $placeholders = []): string
    {
        $regex = [];
        $tokens = $this->getTokens($pattern);
        $delimiter = $this->options[self::REGEX_DELIMITER];
        $modifier = $this->options[self::REGEX_MODIFIER];
        $default_exp = $this->options[self::DEFAULT_REGEX_EXP];
        $capture_right = ($this->options[self::CAPTURE_MODE] & self::CAPTURE_RIGHT) === self::CAPTURE_RIGHT;
        $allow_trail = ($this->options[self::CAPTURE_MODE] & self::ALLOW_OPT_TRAIL) === self::ALLOW_OPT_TRAIL;

        $sep = preg_quote($this->options[self::SEPARATOR_SYMBOL], $delimiter);

        for ($i = 0, $l = count($tokens); $i < $l; $i++) {
            $t = $tokens[$i];
            $p = $tokens[$i - 1] ?? null;
            $n = $tokens[$i + 1] ?? null;

            switch ($t['type']) {
                case 'separator':
                    if ($capture_right) {
                        $regex[] = $sep;
                        if (!$n && $allow_trail) {
                            $regex[] = '?';
                        }
                    } else {
                        if (!$n) {
                            // No more tokens
                            $regex[] = $sep;
                            if ($allow_trail) {
                                $regex[] = '?';
                            }
                        } elseif ($n['type'] !== 'variable') {
                            // Let variables handle capture
                            $regex[] = $sep;
                        }
                    }
                    break;
                case 'variable':
                    if ($t['value'] === null) {
                        $pattern = $t['regex'] ?? $default_exp;
                        $pattern = '(?:' . $pattern . ')';
                    } else {
                        $pattern = $placeholders[$t['value']] ?? $t['regex'] ?? $default_exp;
                        $pattern = '(?P<' . preg_quote($t['value'], $delimiter) . '>(?:' . $pattern . '))';
                    }

                    $is_segment = (!$p || $p['type'] === 'separator') && (!$n || $n['type'] === 'separator');

                    if ($capture_right) {
                        if ($is_segment) {
                            if ($n) {
                                $pattern = '(?:' . $pattern . $sep . ')';
                            }
                            $i++;
                        }
                        if ($t['opt']) {
                            $pattern .= '?';
                        }
                    } else {
                        if ($is_segment) {
                            if ($p) {
                                $pattern = '(?:' . $sep . $pattern . ')';
                            }
                        } elseif ($p && $p['type'] === 'separator') {
                            $pattern = $sep . $pattern;
                        }

                        if ($t['opt']) {
                            $pattern .= '?';
                        }
                    }

                    if (!$n && $allow_trail) {
                        $pattern .= $sep . '?';
                    }

                    $regex[] = $pattern;
                    break;
                default:
                    $regex[] = preg_quote($t['value'], $delimiter);
                    if (!$n && $allow_trail) {
                        $regex[] = $sep . '?';
                    }
                    break;
            }
        }

        if (!$l && $allow_trail) {
            $regex[] = $sep . '?';
        }

        return $delimiter . '^' . implode('', $regex) . '$' . $delimiter . $modifier;
    }

    /**
     * @param string $pattern
     * @return string[]
     */
    public function getNames(string $pattern): array
    {
        $names = [];
        foreach ($this->getTokens($pattern) as $token) {
            if ($token['type'] === 'variable' && $token['value'] !== null) {
                if (!in_array($token['value'], $names)) {
                    $names[] = $token['value'];
                }
            }
        }
        return $names;
    }

    /**
     * @param string $regex
     * @param string $path
     * @return array
     */
    public function getValues(string $regex, string $path): array
    {
        if (!preg_match($regex, $path, $parameters)) {
            return [];
        }

        // Remove full match
        unset($parameters[0]);

        if (count($parameters) === 0) {
            return [];
        }

        $data = [];
        foreach ($parameters as $key => $value) {
            if (is_string($key) && $key !== '') {
                $data[$key] = $value;
            }
        }
        return $data;
    }

    /**
     * @param string $regex
     * @param string $path
     * @return bool
     */
    public function matches(string $regex, string $path): bool
    {
        return (bool)preg_match($regex, $path);
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param string $pattern
     * @return array
     */
    protected function getTokens(string $pattern): array
    {
        $key = md5($pattern);

        if (isset($this->tokens[$key])) {
            return $this->tokens[$key];
        }

        $sym_separator = $this->options[self::SEPARATOR_SYMBOL];
        $sym_opt = $this->options[self::OPT_SYMBOL];
        $sym_start = $this->options[self::START_SYMBOL];
        $sym_end = $this->options[self::END_SYMBOL];
        $assign_symbol = $this->options[self::ASSIGN_SYMBOL];
        $regex_delimiter = $this->options[self::REGEX_DELIMITER];

        $state = 'data';
        $tokens = [];
        $data_marker = 0;

        $opt_var = false;
        $assign_start = -1;

        for ($i = 0, $l = strlen($pattern); $i <= $l; $i++) {
            if ($i === $l) {
                $c = null;
            } else {
                $c = $pattern[$i];
            }
            switch ($state) {
                case 'data':
                    if ($c === $sym_separator) {
                        if ($i - $data_marker > 0) {
                            $tokens[] = [
                                'type' => 'data',
                                'value' => substr($pattern, $data_marker, $i - $data_marker),
                            ];
                        }
                        $tokens[] = [
                            'type' => 'separator',
                            'value' => $c,
                        ];
                        $data_marker = $i + 1;
                    } elseif ($c === $sym_start) {
                        if ($i - $data_marker > 0) {
                            $tokens[] = [
                                'type' => 'data',
                                'value' => substr($pattern, $data_marker, $i - $data_marker),
                            ];
                        }
                        $state = 'var';
                        $data_marker = $i;
                    } elseif ($c === null) {
                        $state = 'eof';
                        $i--;
                    }
                    break;
                case 'var':
                    if ($c === $sym_opt) {
                        $opt_var = true;
                    } elseif ($c === $assign_symbol) {
                        $assign_start = $i;

                        $sym_start_count = 0;
                        $sym_end_count = 0;

                        $last = 0;
                        for ($j = $i + 1; $j < $l; $j++) {
                            if ($pattern[$j] === $sym_start) {
                                $sym_start_count++;
                            } elseif ($pattern[$j] === $sym_end) {
                                $sym_end_count++;
                                if ($sym_end_count > $sym_start_count) {
                                    $last = $j - 1;
                                    break;
                                }
                            }
                        }

                        if ($last > 0) {
                            $i = $last;
                        }

                        unset($j, $sym_start_count, $sym_end_count, $last);
                    } elseif ($c === $sym_end) {

                        // Get placeholder name
                        $start = $data_marker + 1;
                        if ($assign_start >= 0) {
                            $end = $assign_start - $data_marker - 1;
                        } else {
                            $end = $i - $data_marker - 1;
                        }
                        if ($opt_var) {
                            $end--;
                        }

                        $name = substr($pattern, $start, $end);
                        if ($name === '') {
                            $name = null;
                        } elseif (!preg_match('/^[a-z][a-z0-9_]*$/i', $name)) {
                            throw new RuntimeException("Invalid placeholder name: {$name}");
                        }

                        // Get assign value
                        $assign_value = null;
                        if ($assign_start >= 0) {
                            $start = $assign_start + 1;
                            $end = $i - $assign_start - 1;
                            $assign_value = substr($pattern, $start, $end);
                            if ($assign_value === '') {
                                $assign_value = null;
                            } else {

                                $test = $regex_delimiter . '(';
                                $test .= $assign_value;
                                $test .= ')' . $regex_delimiter;

                                if (@preg_match($test, '') === false) {
                                    throw new RuntimeException("Invalid regex for placeholder {$name}, using '{$regex_delimiter}' as delimiter: {$assign_value}");
                                }
                                unset($test);
                            }
                        }

                        if ($name === null && $assign_value === null) {
                            throw new RuntimeException("Anonymous placeholders must have an inline regex. Pattern: {$pattern}");
                        }

                        $tokens[] = [
                            'type' => 'variable',
                            'value' => $name,
                            'opt' => $opt_var,
                            'regex' => $assign_value,
                        ];
                        $opt_var = false;
                        $assign_start = -1;
                        unset($assign_value, $name);
                        $data_marker = $i + 1;
                        $state = 'data';
                    } elseif ($c === null) {
                        $state = 'eof';
                        $i--;
                    }
                    break;
                case 'eof':
                    if ($i - $data_marker > 0) {
                        $tokens[] = [
                            'type' => 'data',
                            'value' => substr($pattern, $data_marker, $i - $data_marker),
                        ];
                    }
                    break;
            }
        }

        $this->tokens[$key] = $tokens;
        return $tokens;
    }
}