<?php
declare(strict_types=1);

namespace App\Services\Cloaking;

abstract class AbstractFileSystemStorage implements \Iterator, \JsonSerializable, \Countable
{
    use TOptions;

    public function __construct($file_name, array $options = null)
    {
        $this->AddOptionsMeta([
            'root' => [
                'type' => 'string',
                'value' => $_SERVER['DOCUMENT_ROOT']
            ]
        ]);
        $this->SetOptionsData($options);
        $this->file_name = $file_name;
        $this->root = $this->getOption('root');
        $this->name = realpath("$this->root/$this->file_name");

        if (!isset(self::$files[$this->name])) {
            $f = new \stdClass();
            $f->items = $f->meta = $f->__data = [];
            $f->data = null;
            $f->changed = false;

            self::$files[$this->name] = $f;
        }

        self::$files[$this->name]->items[] = $this;
    }

    public function __clone()
    {
        throw new \Exception('Can not clone instance of ' . get_class($this));
    }

    final public function GetName()
    {
        return $this->name;
    }

    final public function __debugInfo()
    {
        return ['name' => $this->name, 'data' => $this->jsonSerialize()];
    }

    final public function GetKeys()
    {
        return $this->GetMeta()->GetKeys();
    }

    final public function GetPrimaryKey()
    {
        return $this->GetMeta()->GetPrimaryKey();
    }

    final public function count()
    {
        $d = $this->InitData();
        reset($d->data);
        $i = 0;
        foreach ($d->data as $k => $row) if ($this->RowIsNotEmpty($d, $k)) ++$i;
        return $i;
    }

    final public function jsonSerialize()
    {
        $this->InitData();
        $r = [];
        foreach ($this as $k => $row) $r[$k] = $row;
        return $r;
    }

    final public function ColExists($name, &$col = null)
    {
        $meta = $this->GetMeta();
        $col = ($r = isset($meta->$name)) ? $meta->$name : null;
        return $r;
    }

    final public function ValueExists($col_name, $value)
    {
        if (!isset($this->GetMeta()->$col_name)) throw new \Exception("Undefined property: '$col_name'");
        $values = array_column($this->InitData()->data, $col_name);
        if (null === $value || '' === $value) return false !== array_search($value, $values, true);
        $values = array_filter($values, function ($v) {
            return null !== $v;
        });
        return $values && false !== array_search($value, $values);
    }

    final public function GetMeta(\stdClass &$d = null)
    {
        $d = $this->InitData();
        return self::$meta_data[$this->name];
    }

    final public function Reload()
    {
        $tmp = $this->Load();
        self::$files[$this->name]->meta = $tmp['meta'];
        self::$files[$this->name]->data = $tmp['data'];
        self::$files[$this->name]->keys = $tmp['keys'];
        self::$meta_data[$this->name] = new FileSystemStorageMeta($this, $tmp['meta']);
    }

    final public function __destruct()
    {
        $f = $this->GetFiles();
        foreach ($f->items as $k => $v)
            if ($v === $this) {
                unset($f->items[$k]);
                break;
            }
        if (!$f->items && $f->changed) $this->Save($f);
    }

    final protected function GetFiles()
    {
        return self::$files[$this->name];
    }

    final protected function RowIsNotEmpty(\stdClass $d, $k)
    {
        if (array_key_exists($k, $d->__data)) {
            if (null !== $d->__data[$k]) return true;
        } elseif (null !== $d->data[$k]) return true;
    }

    final protected function Load()
    {
        $tmp = (require $this->name);
        if (is_array($tmp)) {
            if (isset($tmp['meta']) && is_array($tmp['meta'])) return ['meta' => $tmp['meta'], 'data' => isset($tmp['data']) ? $tmp['data'] : [], 'keys' => isset($tmp['keys']) ? $tmp['keys'] : []];
        }
        throw new \Exception("$this->name: invalid file format!");
    }

    final protected function InitData()
    {
        if (null === self::$files[$this->name]->data) {
            $this->Reload();
        }
        return self::$files[$this->name];
    }

    final protected function Save(\stdClass $f)
    {
        $h = fopen($this->GetName(), 'c');
        $t = $this->Load();
        $changed = false;
        foreach ($f->__data as $row_id => $row) {
            if (null === $row) {
                if (isset($t['data'][$row_id])) {
                    unset($t['data'][$row_id]);
                    $changed = true;
                }
            } elseif ($d = $row->Changed()) {
                $changed = true;
                if (!isset($t['data'][$row_id])) $t['data'][$row_id] = [];
                foreach ($row as $k => $v) {
                    if (null === $v) {
                        $m = $this->GetMeta()->$k;
                        if (!$m->IsNullable() && null === $m->value) {
                            if (!$m->auto_increment) throw new \Exception("Field '$k' cannot be null");
                        }
                    }
                    $t['data'][$row_id][$k] = $v;
                }
            }
        }
        if ($changed) {
            $code = '<?php' . PHP_EOL . 'return ' . var_export($t, true) . ';' . PHP_EOL . '?>';
            ftruncate($h, strlen($code));
            fwrite($h, $code);
        }
        fclose($h);
    }

    private $file_name;
    private $root;
    private $name;

    private static $meta_data = [];
    private static $files = [];
}