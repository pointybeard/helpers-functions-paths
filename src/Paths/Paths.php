<?php

declare(strict_types=1);

namespace pointybeard\Helpers\Functions\Paths;

if (!function_exists(__NAMESPACE__."\is_path_absolute")) {
    function is_path_absolute($path)
    {
        return '/' == $path[0] && false == strstr($path, '..');
    }
}

if (!function_exists(__NAMESPACE__."\get_relative_path")) {
    function get_relative_path($from, $to, $strict = true)
    {
        if ($from == $to) {
            return '.';
        } elseif (true == $strict) {
            $fromOrig = $from;
            $toOrig = $to;

            if (false == is_path_absolute($from) && null == ($from = realpath($fromOrig))) {
                throw new \Exception("path {$fromOrig} is relative and does not exist! Make sure path exists or use absolute paths with \$strict set to false)");
            }

            if (false == is_path_absolute($to) && null == ($to = realpath($toOrig))) {
                throw new \Exception("path {$toOrig} is relative and does not exist! Make sure path exists or use absolute paths with \$strict set to false");
            }

            // Strict is false. This means we cannot handle relative paths as input
        } elseif (false == is_path_absolute($to) || false == is_path_absolute($from)) {
            throw new \Exception('Both $from and $to paths must be absolute when $strict is disabled!');
        }

        $bitsFrom = explode(DIRECTORY_SEPARATOR, trim($from, DIRECTORY_SEPARATOR));
        $bitsTo = explode(DIRECTORY_SEPARATOR, trim($to, DIRECTORY_SEPARATOR));

        // Check of these paths have anything in common at all. If they do,
        // we need to do extra work.
        if ($bitsFrom[0] == $bitsTo[0]) {
            // Find the point at which both paths stop being the same
            foreach ($bitsFrom as $depth => $dir) {
                if ($bitsTo[$depth] != $dir) {
                    // Found a point at which the directories no longer match, so
                    // exit the loop.
                    break;
                }
            }

            // Check if $from path is fully contained within $to and if so, return
            // the difference with './' in front
            if (count($bitsTo) > count($bitsFrom) && $depth == count($bitsFrom) - 1) {
                return implode(DIRECTORY_SEPARATOR, array_merge(['.'], array_slice($bitsTo, $depth + 1)));
            }

            // We are left with two paths that have a common point. We need to
            // traverse back down the list of $bitsFrom until we hit that common point
            // then add the bits from $bitsTo to complete the path. Easiest way
            // to do this is to slice off the common parts, then convert $bitsFrom
            // into a series of '../' elements followed by the path remaining in
            // $bitsTo.
            $bitsFrom = array_splice($bitsFrom, $depth);
            $bitsTo = array_splice($bitsTo, $depth);
        }

        // Whatever is left in $bitsFrom is converted into '../' and then contents of $bitsTo is
        // added to the end.
        return implode(DIRECTORY_SEPARATOR, array_merge(array_pad([], -(count($bitsFrom)), '..'), $bitsTo));
    }
}
