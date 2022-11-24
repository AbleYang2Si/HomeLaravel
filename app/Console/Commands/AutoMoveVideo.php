<?php

namespace App\Console\Commands;

use App\Models\TorrentHistory;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AutoMoveVideo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto-move-video';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $videoRules = ['wmv', 'rmvb', 'mp4', 'avi', 'mkv', 'flv'];

    protected $moveFrom;

    protected $moveTo;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->moveFrom = explode(',', env('FILE_MOVE_FROM'));
        $this->moveTo = env('FILE_MOVE_TO');

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $storage = Storage::disk('downloads');
        $toPath = $this->moveTo;

        foreach ($this->moveFrom as $directory) {
            $dFiles = $storage->allFiles($directory);

            foreach ($dFiles as $file) {
                $fileInfo = pathinfo($file);

                try {
                    switch ($this->fileClass($file)) {
                        case 'tv':
                            //判断当前目录下是否有未完成的
                            foreach ($storage->allFiles(dirname($file)) as $tFile) {
                                if ($this->fileClass($tFile) === 'xltd') {
                                    break 2;
                                }
                            }

                            $upDirectory = basename($fileInfo['dirname']);
                            $storage->move($file, $toPath . '/TV Shows/' . $upDirectory . '/' . $fileInfo['basename']);
                            dump('tv:' . $file);
                            break;

                        case 'movie':
                            $storage->move($file, $toPath . '/Movies/' . date('Y-m') . '/' . $fileInfo['basename']);
                            dump('movie:' . $file);
                            break;

                        case 'torrent':
                            $torrentHis = new TorrentHistory();
                            $torrentHis->file_name = basename($fileInfo['dirname']);
                            $torrentHis->file_content = base64_encode($storage->get($file));
                            $torrentHis->save();

                            $storage->delete($file);
                            break;

                        case 'xltd':
                            break;

                        case 'xltd':
                            break;

                        default:
                            if (time() - $storage->lastModified($file) > 24 * 60 * 60) {
                                $storage->delete($file);
                            }
                            break;
                    }
                } catch (Exception $e) {
                    Log::error($file . "\t" . $e->getMessage());
                }
            }

            // 清理空目录
            foreach ($storage->directories($directory) as $tDirectory) {
                if (empty($storage->allFiles($tDirectory))) {
                    $storage->deleteDirectory($tDirectory);
                }
            }
        }

        return 1;
    }

    protected function fileClass($file): string
    {
        $fileInfo = pathinfo($file);

        if (empty($fileInfo['extension'])) {
            return 'other';
        }

        if ($fileInfo['extension'] === 'torrent') {
            return 'torrent';
        }

        if ($fileInfo['extension'] === 'xltd') {
            return 'xltd';
        }

        if ($fileInfo['extension'] === "xltd") {
            return 'xltd';
        }

        if (in_array($fileInfo['extension'], $this->videoRules)) {
            if ($this->isTvShows($fileInfo['filename'])) {
                return 'tv';
            } else {
                return 'movie';
            }
        }

        return 'other';
    }

    /**
     * @param $filename
     * @return bool
     */
    protected function isTvShows($filename): bool
    {
        return preg_match('/\\.[S|s](\d{2})[E|e](\d{2})\./', $filename) !== 0;
    }
}
