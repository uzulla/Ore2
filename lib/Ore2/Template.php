<?php
declare(strict_types=1);
namespace Ore2;

class Template
{
    public $template_dir = __DIR__ . "/template";

    public function __construct(array $config)
    {
        if (isset($config['template_dir']))
            $this->template_dir = $config['template_dir'];
    }

    public function convertoToPhpCode(string $file_name)
    {
        $file_path = $this->template_dir . '/' . $file_name;
        $template = file_get_contents($file_path);

        $chars = preg_split('//u', $template, -1, PREG_SPLIT_NO_EMPTY);// $chars[0]; でマルチバイトあつかいたいね…
        $length = count($chars);

        $code = '';
        for ($i = 0; $length > $i; $i++) {
            if ($chars[$i] === "{" && ($chars[$i + 1] === "{" || $chars[$i + 1] === "%")) {
                // {{ あるいは {% をひろう
                $is_print = ($chars[$i + 1] === "{"); // "{{" はprint
                $i = $i + 2;

                $token_buffer = '';
                for (; $length > $i; $i++) {
                    if (($chars[$i] == "}" || $chars[$i] == "%") && $chars[$i + 1] == "}") {
                        // }} あるいは %} なら token 読み取りを終了
                        echo PHP_EOL;
                        $i++;
                        break;
                    }
                    $token_buffer .= $chars[$i];
                }

                if ($is_print) {
                    $code .= '<?php echo htmlspecialchars($' . trim($token_buffer) . ',ENT_QUOTES, "UTF-8"); ?>';
                } else {
                    $tokens = preg_split('/\s+/u', trim($token_buffer), -1, PREG_SPLIT_NO_EMPTY);
                    if ($tokens[0] === 'for') {
                        $code .= '<?php foreach( $' . $tokens[3] . ' as $' . $tokens[1] . ' ){ ?>';
                    } else if ($tokens[0] === 'endfor') {
                        $code .= '<?php } ?>';
                    }
                }
            } else {
                $code .= $chars[$i];
            }
        }
        return $code;
    }

    public function execute(string $code, array $params)
    {
        $run = function ($code, $params) {
            // 変数をこのスコープに展開する
            foreach ($params as $key => $param) $$key = $param;
            // evalは最初からPHPコードを要求するので、最初に終わらせる
            eval('?>' . $code);
        };

        ob_start();
        $run($code, $params);
        return ob_get_clean();
    }

    public function render(string $file_name, array $params)
    {
        return $this->execute($this->convertoToPhpCode($file_name), $params);
    }
}