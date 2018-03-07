<?php

class TMTCaptcha
{   
    /**
     * 验证码失效时间.过期后图片会被删除
     * @var integer
     */
    public $expiration = 7200;

    /**
     * 干扰线数量
     * @var integer
     */
    public $lines = 3;

    /**
     * 干扰点数量
     * @var integer
     */
    public $spots = 20;

    /**
     * Width of the image
     * @var int
     */
    public $width  = 120;

    /**
     * Height of the image
     * @var int
     */
    public $height = 60;

    /**
     * Path for resource files (fonts, words, etc.)
     * __DIR__."/Resources" by default. For security reasons, is better move this
     * directory to another location outise the web server
     *
     * @var string
     */
    public $resourcesPath;

    /**
     * Min word length (for non-dictionary random text generation)
     * @var int
     */
    public $minWordLength = 4;

    /**
     * Max word length (for non-dictionary random text generation)
     * Used for dictionary words indicating the word-length
     * for font-size modification purposes
     * @var int
     */
    public $maxWordLength = 5;

    /**
     * Sessionname to store the original text
     * @var string
     */
    public $session_var = 'captcha';

    /**
     * Background color in RGB-array
     * @var int[]
     */
    public $backgroundColor = array(255, 255, 255);

    /**
     * Foreground colors in RGB-array
     * @var int[][]
     */
    public $colors = array(
        array(27,  78,  181), // blue
        array(22,  163, 35),  // green
        array(214, 36,  7),   // red
    );

    /**
     * Shadow color in RGB-array or null. For example [0, 0, 0]
     * @var int[]
     */
    public $shadowColor = null;

    /**
     * Horizontal line through the text
     * @var int
     */
    public $lineWidth = 0;

    /**
     * $img_path
     * @var string
     */
    public $img_path = '';

    /**
     * $img_url
     * @var string
     */
    public $img_url = '';


    /**
     * Font configuration
     *
     * - font: TTF file
     * - spacing: relative pixel space between character
     * - minSize: min font size
     * - maxSize: max font size
     * @var array
     */
    public $fonts = array(
        'Antykwa'  => array('spacing' => -3, 'minSize' => 22, 'maxSize' => 22, 'font' => 'AntykwaBold.ttf'),
        'Heineken' => array('spacing' => -2, 'minSize' => 22, 'maxSize' => 22, 'font' => 'Heineken.ttf'),
        'Times'    => array('spacing' => 0, 'minSize' => 22, 'maxSize' => 22, 'font' => 'TimesNewRomanBold.ttf'),
        'Jura'     => array('spacing' => -1, 'minSize' => 23, 'maxSize' => 23, 'font' => 'Jura.ttf'),
    );

    /** Wave configuracion in X and Y axes */
    /** @var int  */
    public $Yperiod    = 12;
    /** @var int  */
    public $Yamplitude = 14;
    /** @var int  */
    public $Xperiod    = 11;
    /** @var int  */
    public $Xamplitude = 5;

    /**
     * Letter rotation clockwise
     * @var int
     */
    public $maxRotation = 8;

    /**
     * Internal image size factor (for better image quality)
     * 1: low, 2: medium, 3: high
     * @var int
     */
    public $scale = 1;

    /**
     * Blur effect for better image quality (but slower image processing).
     * Better image results with scale=3
     * @var bool
     */
    public $blur = false;

    /**
     * Debug?
     * @var bool
     */
    public $debug = false;
    

    /**
     * GD image
     * @var resource
     */
    public $im;


    /**
     * microtime
     */
    public $ini;


    public function __construct($config = array())
    {
        if ($config) {
            foreach ($config as $key => $val)
            {
                $this->$key = $val;
            }
        }
        $this->resourcesPath = __DIR__.'/Resources';
    }

    /**
     * Generates captcha and outputs it to the browser.
     * @return string Text answer of generated captcha
     */
    public function createImage()
    {
        $this->ini = microtime(true);

        $this->remove();

        /** Initialization */
        $this->imageAllocate();

        /**create image line*/
        $this->drawLine();
        $this->getPixels();
        /** Text insertion */
        $this->text = $this->getCaptchaText();
        $fontcfg  = $this->fonts[array_rand($this->fonts)];
        $this->writeText($this->text, $fontcfg);
        /** Transformations */
        if (!empty($this->lineWidth)) {
            $this->writeLine();
        }
        $this->waveImage();
        if ($this->blur && function_exists('imagefilter')) {
            imagefilter($this->im, IMG_FILTER_GAUSSIAN_BLUR);
        }
        $this->reduceImage();
        if ($this->debug) {
            imagestring(
                $this->im,
                1,
                1,
                $this->height-8,
                "$this->text {$fontcfg['font']} ".round((microtime(true)-$this->ini)*1000)."ms",
                $this->GdFgColor
            );
        }
        // -----------------------------------
        //  Generate the image
        // -----------------------------------
        $url = $this->save();
        $this->cleanup();

        $img_filename = $this->ini.'.jpg';
        $img = '<img src="'.$url.'" style="width: '.$this->width.'; height: '.$this->height .'; border: 0;" alt=" " />';
        $output = array(
            'word'     => $this->text,
            'time'     => $this->ini,
            'image'    => $img,
            'img_url'  => $url,
            'filename' => $img_filename,
        );
        return $output;
    }

    /**
     * Creates the image resources
     */
    protected function imageAllocate()
    {
        // Cleanup
        if (!empty($this->im)) {
            imagedestroy($this->im);
        }

        $this->im = imagecreatetruecolor($this->width*$this->scale, $this->height*$this->scale);

        // Background color
        $this->GdBgColor = imagecolorallocate(
            $this->im,
            $this->backgroundColor[0],
            $this->backgroundColor[1],
            $this->backgroundColor[2]
        );
        imagefilledrectangle($this->im, 0, 0, $this->width*$this->scale, $this->height*$this->scale, $this->GdBgColor);

        // Foreground color
        $color           = $this->colors[mt_rand(0, sizeof($this->colors)-1)];
        $this->GdFgColor = imagecolorallocate($this->im, $color[0], $color[1], $color[2]);

        // Shadow color
        if (!empty($this->shadowColor) && is_array($this->shadowColor) && sizeof($this->shadowColor) >= 3) {
            $this->GdShadowColor = imagecolorallocate(
                $this->im,
                $this->shadowColor[0],
                $this->shadowColor[1],
                $this->shadowColor[2]
            );
        }
    }


    /**
     * Draw lines over the image
     */
    protected function drawLine()
    {
        for($i = 0;$i < $this->lines;$i++){
            //分配颜色
            $line= imagecolorallocate($this->im,mt_rand(100,200),mt_rand(100,200),mt_rand(100,200));
            //制作线段
            imageline($this->im,mt_rand(0,$this->width),mt_rand(0,$this->height),mt_rand(0,$this->width),mt_rand(0,$this->height),$line);
        }
    }

    /**
     * Draw pixel over the image
     */
    protected function getPixels(){
        for($i = 0;$i < $this->spots;$i++){
            //分配颜色
            $pixel= imagecolorallocate($this->im,mt_rand(100,200),mt_rand(100,200),mt_rand(100,200));
            //制作
            imagesetpixel($this->im,mt_rand(0,$this->width),mt_rand(0,$this->height),$pixel);
        }
    }

    /**
     * save the images
     * @return
     */
    protected function save()
    {
        if ($this->img_path === '' OR $this->img_url === '' OR ! is_dir($this->img_path) OR ! is_really_writable($this->img_path) OR ! extension_loaded('gd'))
        {
            throw new Exception($this->img_path.' can not access , permission deny');
        }
        if (function_exists('imagejpeg'))
        {
            $img_filename = $this->ini.'.jpg';
            imagejpeg($this->im, $this->img_path.$img_filename);
        }
        else
        {
            return FALSE;
        }

        return $this->img_url.$img_filename;
    }


    protected function remove()
    {
        // -----------------------------------
        // Remove old images
        // -----------------------------------
        $current_dir = @opendir($this->img_path);
        while ($filename = @readdir($current_dir))
        {
            if (substr($filename, -4) === '.jpg' && (str_replace('.jpg', '', $filename) + $this->expiration) < $this->ini)
            {
                @unlink($this->img_path.$filename);
            }
        }
        @closedir($current_dir);
    }



    /**
     * Text generation
     *
     * @return string Text
     */
    protected function getCaptchaText()
    {
        $text = $this->getRandomCaptchaText();
        return $text;
    }

    /**
     * Random text generation
     *
     * @param int|null Text length
     * @return string Text
     */
    protected function getRandomCaptchaText($length = null)
    {
        if (empty($length)) {
            $length = rand($this->minWordLength, $this->maxWordLength);
        }

        $words  = "abcdefghijlmnopqrstvwyz";
        $vocals = "aeiou";

        $text  = "";
        $vocal = rand(0, 1);
        for ($i=0; $i<$length; $i++) {
            if ($vocal) {
                $text .= substr($vocals, mt_rand(0, 4), 1);
            } else {
                $text .= substr($words, mt_rand(0, 22), 1);
            }
            $vocal = !$vocal;
        }
        return $text;
    }

    /**
     * Horizontal line insertion
     */
    protected function writeLine()
    {
        $x1 = $this->width*$this->scale*.15;
        $x2 = $this->textFinalX;
        $y1 = rand($this->height*$this->scale*.40, $this->height*$this->scale*.65);
        $y2 = rand($this->height*$this->scale*.40, $this->height*$this->scale*.65);
        $width = $this->lineWidth/2*$this->scale;

        for ($i = $width*-1; $i <= $width; $i++) {
            imageline($this->im, $x1, $y1+$i, $x2, $y2+$i, $this->GdFgColor);
        }
    }

    /**
     * Text insertion
     */
    protected function writeText($text, $fontcfg = array())
    {
        if (empty($fontcfg)) {
            // Select the font configuration
            $fontcfg  = $this->fonts[array_rand($this->fonts)];
        }

        // Full path of font file
        $fontfile = $this->resourcesPath.'/fonts/'.$fontcfg['font'];


        /** Increase font-size for shortest words: 9% for each glyp missing */
        $lettersMissing = $this->maxWordLength-strlen($text);
        $fontSizefactor = 1+($lettersMissing*0.09);

        // Text generation (char by char)
        $x      = 20*$this->scale;
        $y      = round(($this->height*27/40)*$this->scale);
        $length = strlen($text);
        for ($i=0; $i<$length; $i++) {
            $degree   = rand($this->maxRotation*-1, $this->maxRotation);
            $fontsize = rand($fontcfg['minSize'], $fontcfg['maxSize'])*$this->scale*$fontSizefactor;
            $letter   = substr($text, $i, 1);

            if ($this->shadowColor) {
                $coords = imagettftext(
                    $this->im,
                    $fontsize,
                    $degree,
                    $x+$this->scale,
                    $y+$this->scale,
                    $this->GdShadowColor,
                    $fontfile,
                    $letter
                );
            }
            $coords = imagettftext(
                $this->im,
                $fontsize,
                $degree,
                $x,
                $y,
                $this->GdFgColor,
                $fontfile,
                $letter
            );
            $x += ($coords[2]-$x) + ($fontcfg['spacing']*$this->scale);
        }

        $this->textFinalX = $x;
    }

    /**
     * Wave filter
     */
    protected function waveImage()
    {
        // X-axis wave generation
        $xp = $this->scale*$this->Xperiod*rand(1, 3);
        $k = rand(0, 100);
        for ($i = 0; $i < ($this->width*$this->scale); $i++) {
            imagecopy($this->im, $this->im,
                $i-1, sin($k+$i/$xp) * ($this->scale*$this->Xamplitude),
                $i, 0, 1, $this->height*$this->scale);
        }

        // Y-axis wave generation
        $k = rand(0, 100);
        $yp = $this->scale*$this->Yperiod*rand(1,2);
        for ($i = 0; $i < ($this->height*$this->scale); $i++) {
            imagecopy($this->im, $this->im,
                sin($k+$i/$yp) * ($this->scale*$this->Yamplitude), $i-1,
                0, $i, $this->width*$this->scale, 1);
        }
    }

    /**
     * Reduce the image to the final size
     */
    protected function reduceImage()
    {
        $imResampled = imagecreatetruecolor($this->width, $this->height);
        imagecopyresampled($imResampled, $this->im,
            0, 0, 0, 0,
            $this->width, $this->height,
            $this->width*$this->scale, $this->height*$this->scale
        );
        imagedestroy($this->im);
        $this->im = $imResampled;
    }

    /**
     * File generation
     */
    protected function writeImage()
    {
        header('Content-Disposition:inline;filename="captcha.jpg"');
        header('Content-type: image/jpeg');
        imagejpeg($this->im, null, 80);
    }

    /**
     * Cleanup
     */
    protected function cleanup()
    {
        imagedestroy($this->im);
    }
}
