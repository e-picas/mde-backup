<?php
/*
 * This file is part of the PHP-MarkdownExtended package.
 *
 * (c) Pierre Cassat <me@e-piwi.fr> and contributors
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace testsMarkdownExtended\Grammar;

use \testsMarkdownExtended\MarkdownExtendedBaseTest;

class HardbreakTest extends MarkdownExtendedBaseTest
{

    public function testCreate()
    {
        $this->processParseTest(
            "
This is a definition with two paragraphs. Lorem ipsum
dolor sit amet, consectetuer adipiscing elit. Aliquam
hendrerit mi posuere lectus.

Vestibulum enim wisi, viverra nec, fringilla in, laoreet
vitae, risus.
        ",
            '<p>This is a definition with two paragraphs. Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aliquam hendrerit mi posuere lectus.</p><p>Vestibulum enim wisi, viverra nec, fringilla in, laoreet vitae, risus.</p>',
            'Hard break fails!',
            true,
            true
        );

    }
    
}
