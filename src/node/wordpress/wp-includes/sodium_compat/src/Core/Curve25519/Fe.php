<?php
 if (class_exists('ParagonIE_Sodium_Core_Curve25519_Fe', false)) { return; } class ParagonIE_Sodium_Core_Curve25519_Fe implements ArrayAccess { protected $container = array(); protected $size = 10; public static function fromArray($array, $save_indexes = null) { $count = count($array); if ($save_indexes) { $keys = array_keys($array); } else { $keys = range(0, $count - 1); } $array = array_values($array); $obj = new ParagonIE_Sodium_Core_Curve25519_Fe(); if ($save_indexes) { for ($i = 0; $i < $count; ++$i) { $obj->offsetSet($keys[$i], $array[$i]); } } else { for ($i = 0; $i < $count; ++$i) { $obj->offsetSet($i, $array[$i]); } } return $obj; } public function offsetSet($offset, $value) { if (!is_int($value)) { throw new InvalidArgumentException('Expected an integer'); } if (is_null($offset)) { $this->container[] = $value; } else { $this->container[$offset] = $value; } } public function offsetExists($offset) { return isset($this->container[$offset]); } public function offsetUnset($offset) { unset($this->container[$offset]); } public function offsetGet($offset) { if (!isset($this->container[$offset])) { $this->container[$offset] = 0; } return (int) ($this->container[$offset]); } public function __debugInfo() { return array(implode(', ', $this->container)); } } 