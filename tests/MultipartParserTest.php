<?php

namespace React\Tests\Http;

use React\Http\MultipartParser;

class MultipartParserTest extends TestCase {

    public function testPostKey() {

        $boundary = "---------------------------5844729766471062541057622570";

        $data  = "--$boundary\r\n";
        $data .= "Content-Disposition: form-data; name=\"users[one]\"\r\n";
        $data .= "\r\n";
        $data .= "single\r\n";
        $data .= "--$boundary\r\n";
        $data .= "Content-Disposition: form-data; name=\"users[two]\"\r\n";
        $data .= "\r\n";
        $data .= "second\r\n";
        $data .= "--$boundary--\r\n";

        $parser = new MultipartParser($data, $boundary);
        $parser->parse();

        $this->assertEmpty($parser->getFiles());
        $this->assertEquals(
            ['users' => ['one' => 'single', 'two' => 'second']],
            $parser->getPost()
        );
    }

    public function testFileUpload() {

        $file = base64_decode("R0lGODlhAQABAIAAAP///wAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw==");

        $boundary = "---------------------------12758086162038677464950549563";

        $data  = "--$boundary\r\n";
        $data .= "Content-Disposition: form-data; name=\"user\"\r\n";
        $data .= "\r\n";
        $data .= "single\r\n";
        $data .= "--$boundary\r\n";
        $data .= "Content-Disposition: form-data; name=\"user2\"\r\n";
        $data .= "\r\n";
        $data .= "second\r\n";
        $data .= "--$boundary\r\n";
        $data .= "Content-Disposition: form-data; name=\"users[]\"\r\n";
        $data .= "\r\n";
        $data .= "first in array\r\n";
        $data .= "--$boundary\r\n";
        $data .= "Content-Disposition: form-data; name=\"users[]\"\r\n";
        $data .= "\r\n";
        $data .= "second in array\r\n";
        $data .= "--$boundary\r\n";
        $data .= "Content-Disposition: form-data; name=\"file\"; filename=\"User.php\"\r\n";
        $data .= "Content-Type: text/php\r\n";
        $data .= "\r\n";
        $data .= "<?php echo 'User';\r\n";
        $data .= "\r\n";
        $data .= "--$boundary\r\n";
        $data .= "Content-Disposition: form-data; name=\"files[]\"; filename=\"blank.gif\"\r\n";
        $data .= "Content-Type: image/gif\r\n";
        $data .= "\r\n";
        $data .= $file . "\r\n";
        $data .= "--$boundary\r\n";
        $data .= "Content-Disposition: form-data; name=\"files[]\"; filename=\"User.php\"\r\n";
        $data .= "Content-Type: text/php\r\n";
        $data .= "\r\n";
        $data .= "<?php echo 'User';\r\n";
        $data .= "\r\n";
        $data .= "--$boundary--\r\n";

        $parser = new MultipartParser($data, $boundary);
        $parser->parse();

        $this->assertEquals(2, count($parser->getFiles()));
        $this->assertEquals(2, count($parser->getFiles()['files']));
        $this->assertEquals(
            ['user' => 'single', 'user2' => 'second', 'users' => ['first in array', 'second in array']],
            $parser->getPost()
        );

        $uploaded_blank = $parser->getFiles()['files'][0];

        $this->assertEquals($file, file_get_contents($uploaded_blank['tmp_name']));
        
        $uploaded_blank['tmp_name'] = 'file'; 
        $expected_file = [
            'name' => 'blank.gif',
            'type' => 'image/gif',
            'tmp_name' => 'file',
            'error' => 0,
            'size' => 43,
        ];

        $this->assertEquals($expected_file, $uploaded_blank);
    }
}
