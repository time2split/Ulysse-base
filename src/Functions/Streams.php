<?php
namespace Ulysse\Base\Functions\Streams;

/**
 * Ouvre un nouveau stream à partir de $stream.
 *
 * @param resource $stream
 */
function openStreamCopy($stream)
{
	return \openStreamFromMetaData(\stream_get_meta_data($stream));
}

/**
 * Ouvre un nouveau stream à partir de $metaData, un array retourné par stream_get_meta_data.
 *
 * @link http://php.net/manual/en/function.stream-get-meta-data.php
 *
 * @param array $metaData
 */
function openStreamFromMetaData(array $metaData)
{
	return \fopen($metaData['uri'], $metaData['mode']);
}