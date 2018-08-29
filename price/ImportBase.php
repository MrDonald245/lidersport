<?php
/**
 * Created by Eugene.
 * User: eugene
 * Date: 27/08/18
 * Time: 12:15
 */

include('../api/Simpla.php');

/**
 * Class ImportBase - базовый объект для всех классов импорта.
 */
abstract class ImportBase
{
    /**
     * @var Simpla
     */
    protected $simpla;

    /**
     * Путь к файлу импорта.
     *
     * @var string|null $importFilePath
     */
    private $importFilePath;

    /**
     * Разделитель CSV файла.
     *
     * @var null|string $csv_delimiter
     */
    private $csvDelimiter;

    /**
     * ImportBase constructor.
     *
     * @param string|null $import_file_path
     * @param string|null $csv_delimiter
     */
    public function __construct($import_file_path = null, $csv_delimiter = null)
    {
        $this->simpla         = new Simpla();
        $this->importFilePath = $import_file_path;
        $this->csvDelimiter   = $csv_delimiter;
    }

    /**
     * Выполнить импорт.
     *
     * @return void
     */
    abstract public function process();

    /**
     * @param callable $callback функция, которая вызывается каждую строку при переборе файла импорта.
     *
     * @throws Exception
     */
    protected function read_csv($callback)
    {
        if (!$callback) {
            throw new Exception('методу read_file необходим параметр $callback');
        }

        $filename = $this->get_import_file_path();

        // Установка кодировки файла.
        setlocale(LC_ALL, 'ru_RU.' . $this->get_file_charset($filename));

        // Чтение файла построчно.
        $handle = fopen($filename, "r");
        if ($handle) {
            $line_number = 0;
            $header      = array();

            while (($line = fgets($handle)) !== false) {

                if ($line_number == 0) {
                    $header = str_getcsv($line, $this->csvDelimiter);   // Заголовок csv файла.
                } else {
                    $csv_body         = str_getcsv($line, $this->csvDelimiter);
                    $header_with_body = array_combine($header, $csv_body);

                    $callback($header_with_body, $line_number);        // Строка с данными CSV файла.
                }

                $line_number++;
            }

            fclose($handle);
        } else {
            throw new Exception("Невозможно открыть файл \"$filename\"");
        }
    }

    /**
     * Получить полный путь к файлу импорта.
     *
     * @return string
     */
    protected function get_import_file_path()
    {
        return $this->simpla->config->root_dir . $this->importFilePath;
    }

    /**
     * Узнать кодировку файла.
     *
     * @param string $filename
     *
     * @return string `UTF8` или `CP1251`
     */
    private function get_file_charset($filename)
    {
        // Узнаем какая кодировка у файла
        $fh         = fopen($filename, 'r');
        $teststring = fread($fh, 2);
        fclose($fh);

        // Кодировки
        if (preg_match('//u', $teststring)) {
            $charset = 'UTF8';
        } else {
            $charset = 'CP1251';
        }

        return $charset;
    }
}