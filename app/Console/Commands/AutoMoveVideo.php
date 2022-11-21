<?php

namespace App\Console\Commands;

use App\Models\TorrentHistory;
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

    protected $moveFrom = [
        '迅雷下载',
    ];

    protected $moveTo = 'video';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $storage = Storage::disk('downloads');
        $toPath = $this->moveTo;

        foreach ($this->moveFrom as $directory) {
            $dFiles = $storage->allFiles($directory);
            
            foreach($dFiles as $file) {
                $fileInfo = pathinfo($file);
                
                try {
                    switch ($this->fileClass($file)) {
                        case 'tv':
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

                        default:
                            $storage->delete($file);
                            break;
                    }
                } catch (\Exception $e) {
                    Log::error($file . "\t" . $e->getMessage());
                }
            }
        }
    }

    protected function fileClass($file)
    {
        $fileInfo = pathinfo($file);
        
        if (empty($fileInfo['extension'])) {
            return 'other';
        }

        if ($fileInfo['extension'] === 'torrent') {
            return 'torrent';
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

    protected function isTvShows($filename) {
        return preg_match('/\\.(S|s)(\d{2})(E|e)(\d{2})\./', $filename) !== 0;
    }
}
