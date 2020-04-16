<?php

namespace Armincms\Storage\Nova;

 
use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID; 
use Laravel\Nova\Fields\Text; 
use Laravel\Nova\Fields\Number; 
use Laravel\Nova\Fields\Select; 
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Heading;  
use Laravel\Nova\Fields\SecretValue;  

use Epartment\NovaDependencyContainer\HasDependencies;
use Epartment\NovaDependencyContainer\NovaDependencyContainer; 
use Armincms\Json\Json;
use Inspheric\Fields\Url;

class StorageDisk extends Resource
{
    use HasDependencies;

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'Armincms\\Storage\\StorageDisk';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'label';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'label', 'name'
    ];  

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),  

            Select::make(__("Driver"), 'driver')
                ->options([
                    'ftp'   => "FTP",
                    'sftp'  => "SFTP", 
                    'local' => "Local",
                    's3'    => 'Amazon s3',
                    'rackspace' => "Rackspace",

                ])
                ->rules('required')
                ->required()
                ->displayUsingLabels()
                ->default('ftp'),

            Text::make(__("Name"), 'name')
                ->rules('required', 'alpha_dash')
                ->required(),

            Text::make(__("Label"), "label")
                ->rules("required")
                ->required(),

            Heading::make(__("Disk Configurations")), 

            $this->localFields(),

            $this->ftpFields(),

            $this->sftpFields(),

            $this->amazonFields(),

            $this->rackspaceFields(), 
        ];
    }  


    public function localFields()
    {  
        return NovaDependencyContainer::make(
            Json::make('local', [
                Text::make("Root")
                    ->required()
                    ->rules("required")
                    ->default(config('filesystems.disks.public.root'))
                    ->help(__("The relative paths will be considered as storage sub-directory")),

                Url::make("Url")
                    ->required()
                    ->rules('required')
                    ->default(config('filesystems.disks.public.url')),

                Text::make("Visibility")
                    ->required()
                    ->rules('required')
                    ->default(config('filesystems.disks.public.visibility'))
            ])->toArray(), 
        )->dependsOn('driver', 'local'); 
    }

    public function ftpFields()
    {
        return NovaDependencyContainer::make(
            Json::make('ftp', $this->defaultFtpFields())->toArray(), 
        )->dependsOn('driver', 'ftp');
    } 

    public function sftpFields()
    { 
        $fields = array_merge([ 
            Text::make("privateKey")
                ->required()
                ->rules("required")

        ], $this->defaultFtpFields());

        return NovaDependencyContainer::make(
            Json::make('sftp', $fields)->toArray(), 
        )->dependsOn('driver', 'sftp');
    } 

    public function defaultFtpFields()
    {
        return [ 
            Url::make('host')
                ->rules('required')
                ->required(),

            Text::make('username')
                ->rules('required')
                ->required(), 

            Text::make('password')
                ->rules('required')
                ->required(),  

            Text::make('root'),  

            Number::make('port')
                ->default(21),

            Number::make('timeout')
                ->default(30)
                ->min(15)
                ->rules("required", "min:2")
                ->required(),

            Boolean::make('passive')
                ->default(true),

            Boolean::make('ssl')
                ->default(true), 
        ];
    }

    public function amazonFields()
    {
        return NovaDependencyContainer::make( 
            Json::make('amazon', [ 
                Text::make("Key")
                    ->required()
                    ->rules('required')
                    ->default(config('filesystems.disks.s3.key')),

                Text::make("Secret")
                    ->required()
                    ->rules('required')
                    ->default(config('filesystems.disks.s3.secret')),

                Text::make("Region")
                    ->required()
                    ->rules('required')
                    ->default(config('filesystems.disks.s3.region')),

                Text::make("Bucket")
                    ->required()
                    ->rules('required')
                    ->default(config('filesystems.disks.s3.bucket')),

                Url::make("url")
                    ->required()
                    ->rules('required')
                    ->default(config('filesystems.disks.s3.url')), 
            ])->toArray()
        )->dependsOn('driver', 's3');
    }

    public function rackspaceFields()
    {
        return NovaDependencyContainer::make(   
            Json::make('rackspace', [ 
                Text::make('username')
                    ->rules('required')
                    ->required(), 

                Text::make('key')
                    ->rules('required')
                    ->required(),  

                Text::make('container')
                    ->rules('required')
                    ->required(),  

                Url::make('endpoint')
                    ->rules('required', 'url')
                    ->required()
                    ->default('https://identity.api.rackspacecloud.com/v2.0/')
                    ->textAlign('left'),

                Text::make('region')
                    ->rules('required')
                    ->required()
                    ->default('IAD'),

                Text::make('url_type')
                    ->rules('required')
                    ->required()
                    ->default('publicURL'),  
            ])->toArray() 
        )->dependsOn('driver', 'rackspace');
    }
}
