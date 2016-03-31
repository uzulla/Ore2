<?php
namespace Ore2;

/* sample
<!doctype html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>Document</title>
</head>
<body>
    {{ name }}

    {% for item in list %}
        {{ item }}
    {% endfor %}
</body>
</html>
*/

class Template
{
    public $template_dir = __DIR__."/template";

    public function __construct($config)
    {
        if(isset($config['template_dir']))
            $this->template_dir = $config['template_dir'];
    }

    public function parse($file_name){
        $file_path = $this->template_dir . '/' . $file_name;
        $template = file_get_contents($file_path);

        $chars = preg_split('//u', $template, -1, PREG_SPLIT_NO_EMPTY);// $chars[0]; でマルチバイトあつかいたいね…
        $length = count($chars);

        $buffer = '';
        for ($i = 0; $length > $i; $i++) {

            if ($chars[$i] === "{" && $chars[$i + 1] === "{") {
                $i++;
                $i++;
                $expression_buffer = '';
                for (; $length > $i; $i++) {
                    if ($chars[$i] == "}" && $chars[$i + 1] == "}") {
                        echo PHP_EOL;
                        $i++;
                        break;
                    }
                    $expression_buffer .= $chars[$i];
                }
                $buffer .= '<?php echo htmlspecialchars($' . trim($expression_buffer) . ',ENT_QUOTES, "UTF-8"); ?>';
                continue;

            } else if ($chars[$i] === "{" && $chars[$i + 1] === "%") {
                $i++;
                $i++;
                $expression_buffer = '';
                for (; $length > $i; $i++) {
                    if ($chars[$i] == "%" && $chars[$i + 1] == "}") {
                        echo PHP_EOL;
                        $i++;
                        break;
                    }
                    $expression_buffer .= $chars[$i];
                }
                $tokens = preg_split('/\s+/u', trim($expression_buffer), -1, PREG_SPLIT_NO_EMPTY);
                if ($tokens[0] === 'for') {
                    $buffer .= '<?php foreach( $' . $tokens[3] . ' as $' . $tokens[1] . ' ){ ?>';
                } else if ($tokens[0] === 'endfor') {
                    $buffer .= '<?php } ?>';
                }
                continue;
            } else {
                $buffer .= $chars[$i];
            }
        }
        return $buffer;
    }

    public function execute($buffer, $params){
//        echo $buffer . PHP_EOL;
        $run = function ($code, $params) {
            foreach ($params as $key => $param) {
                $$key = $param;
            }
            eval('?>' . $code);
        };

        ob_start();
        $run($buffer, $params);
        return ob_get_clean();
    }

    public function render($file_name, $params)
    {
        return $this->execute($this->parse($file_name), $params);
    }
}