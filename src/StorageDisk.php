<?php

namespace Armincms\Storage;

use Illuminate\Database\Eloquent\Model;

class StorageDisk extends Model
{
    protected $guarded = [];

    protected $casts = [
    	'config' => 'json',
    ]; 

    protected static $drivers = [
    	'amazon', 'ftp', 'sftp', 'local', 'rackspace'
	];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function() {

            $disks = static::get()->mapWithKeys(function($disk) {
                return [
                    $disk->name => array_merge(['driver' => $disk->driver], (array) $disk->config)
                ];
            })->toArray();

            ob_start();

            var_export($disks); 

            $data = preg_replace('/\s*array/', '', ob_get_clean()); 
            $data = str_replace(['(', ')'], ['[',']'], $data);

            \File::put(__DIR__.'/../config/disks.php', '<?php return ' .$data. ';'); 
        });
    }

    public static function drivers()
    { 
        return static::$drivers;
    }

    public function pushDdriver($driver)
    {
        static::$drivers = array_merge(
            static::$drivers, is_array($driver) ? $driver : func_get_args()
        );

        return new static;
    }

    /**
     * Set a given attribute on the model.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    public function setAttribute($key, $value)
    {
    	parent::setAttribute($key, $value);

    	if(! in_array($key, static::drivers())) {
    		return;
    	}

    	if($key === $this->driver) {
    		$this->attributes['config'] = $value;
    	}

    	unset($this->attributes[$key]);
    } 

    /**
     * Get an attribute from the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function getAttribute($key)
    { 
        if(in_array($key, static::drivers()) && $this->driver === $key) {
            return $this->config;
        }

    	return parent::getAttribute($key);
    }
}
