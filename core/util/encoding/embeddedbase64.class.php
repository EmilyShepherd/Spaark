<?php namespace Spaark\Core\Model\Encoding;

class Base64NotFoundException extends Exception() { }

/**
 * Represents a base64 encoded string that is embedded within a file
 */
class EmeddedBase64 extends Base64
{
    /**
     * @readonly
     */
    private $header;

    /**
     * @readonly
     */
    private $footer;

    /**
     * Searches for what looks like a base64 encoded string, and decodes
     * it
     *
     * It will favour strings surrounded by --BEGIN *-- --END *--, if
     * found
     *
     * @param string $data The data to decode
     * @return string The decoded base64 string
     * @throws Base64NotFoundException If no appropriate string is found
     */
    public function decode($data)
    {
        preg_match
        (
              '#'
            .   '(-+) *BEGIN([ A-Z]*) *\1'
            .   '(([^:]*: *[^\r\n]*\s*[\r\n]+)*)'
            .   '('
            .       '((?<=[\^\r\n])[ \t]*[a-zA-Z0-9/+]+[$\r\n]+)+'
            .       '(?<=[\^\r\n])[ \t]*([a-zA-Z0-9/+]+)?'
            .       '\s*=?\s*=?'
            .   ')[$\r\n]+'
            .   '[ \t]*\1 *END\2 *\1'
            . '#',
            $data,
            $matches,
            PREG_OFFSET_CAPTURE
        );

        if (count($matches[0]))
        {
            $output = $matches[6][0];
            $offset = $matches[6][1];
        }
        else
        {
            preg_match
            (
                  '#'
                .   '((?<=[\^\r\n])[ \t]*[a-zA-Z0-9/+]+[$\r\n]+)+'
                .   '(?<=[\^\r\n])[ \t]*([a-zA-Z0-9/+]+)?'
                .   '\s*=?\s*=?[$\r\n]+'
                . '#',
                $data,
                $matches,
                PREG_OFFSET_CAPTURE
            );

            if (count($matches[0]))
            {
                $output = trim($matches[0][0]);
                $offset = $matches[0][1];
            }
            else
            {
                throw new Base64NotFoundException();
            }
        }

        $this->header = substr($data, 0, $offset);
        $this->footer = substr($data, $offset + strlen($output));
        
        return parent::decode($output);
    }

    /**
     * Encodes the given data, surrounding it with the saved header and
     * footer
     *
     * @param string $data The data to encode
     * @return string The encoded data
     */
    public function encode($data)
    {
        return $this->header . parent::encode($data) . $this->footer;
    }
}