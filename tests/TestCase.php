<?php

use Laracasts\TestDummy\Factory;
use KlinkDMS\User;
use KlinkDMS\Capability;
use Illuminate\Support\Facades\Artisan;

class TestCase extends Illuminate\Foundation\Testing\TestCase {


	protected $artisan = null;
    
    protected $baseUrl = 'http://localhost/';
	
	public function setUp()
	{

		parent::setUp();

        $this->resetEvents();

		// $artisan->call('migrate',array('-n'=>true));

		// $artisan->call('db:seed',array('-n'=>true));
		//dd(env('DB_NAME'));
		
		//let's create some users to simulate different kinds of users
	}

	/**
	 * Creates the application.
	 *
	 * @return \Illuminate\Foundation\Application
	 */
	public function createApplication()
	{
		$app = require __DIR__.'/../bootstrap/app.php';

		$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

		return $app;
	}
    
    
    protected function seedDatabase(){
        $artisan = app()->make('Illuminate\Contracts\Console\Kernel');
// 
// 		// $artisan->call('migrate',array('-n'=>true));
// 
		$artisan->call('db:seed',array('-n'=> true));
    }
    
	
	
	protected function createAdminUser(){
        
        if( Capability::all()->isEmpty() ){
            $this->seedDatabase();
            
            var_dump( 'Database seeding completed' );
        }
        
        $admin_user = factory(\KlinkDMS\User::class)->create();
		
		$admin_user->addCapabilities( Capability::$ADMIN );
		
		return $admin_user;
	}
	
	protected function createUser($capabilities, $user_params = []){
        
        if( Capability::all()->isEmpty() ){
            $this->seedDatabase();
            
            var_dump( 'Database seeding completed' );
        }
		
		$user = factory(\KlinkDMS\User::class)->create( $user_params );
		
		$user->addCapabilities( $capabilities );
		
		return $user;
	}


    protected function createUsers($capabilities, $count, $user_params = []){
        
        if( Capability::all()->isEmpty() ){
            $this->seedDatabase();
        }
		
		$users = factory(\KlinkDMS\User::class, $count)->create( $user_params );

        $users->each(function($el) use($capabilities){
            $el->addCapabilities( $capabilities );
        });

		return $users;
	}


    protected function createDocument(User $user, $visibility = 'private'){

        $template = base_path('tests/data/example.pdf');
        $destination = storage_path('documents/example-document.pdf');

        copy($template, $destination);

        $file = factory('KlinkDMS\File')->create([
            'user_id' => $user->id,
            'original_uri' => '',
            'path' => $destination,
        ]);
        
        $doc = factory('KlinkDMS\DocumentDescriptor')->create([
            'owner_id' => $user->id,
            'file_id' => $file->id,
            'hash' => $file->hash,
            'is_public' => $visibility === 'private' ? false : true
        ]);

        return $doc;

    }

    protected function createCollection(User $user, $is_personal = true, $childs = 0){

        $service = app('Klink\DmsDocuments\DocumentsService');

        $group = $service->createGroup($user, 'Collection of user ' . $user->id, null, null, $is_personal);

        if($childs > 0){

            for ($i=0; $i < $childs; $i++) { 
                $service->createGroup($user, 'Child ' . $user->id . '-' . $group->id . '-' . $i, null, $group, $is_personal);
            }

        }

        return $group;

    }
    
    
    public function assertViewName($expected){
        
        try{
        
            if( isset( $this->response ) && !is_string($this->response->original) && !empty( $this->response->original->name() ) ){
                
                $this->assertEquals($expected, $this->response->original->name() );
                
                return;
            }
            
            $this->fail('Response does not have a view');

        }catch(\Exception $e){
            $this->fail('Exception while checking view name assertion. ' . $e->getMessage());
        }
        
        
    }




    public function resetEvents()
    {
        $models = $this->getModels();

        foreach ($models as $model)
        {
            call_user_func([$model, 'flushEventListeners']);

            call_user_func([$model, 'boot']);
        }
    }
    
    protected function getModels()
    {
        // Replace with your models directory if you've moved it.
        $files = \File::files(base_path() . '/app/models');
        
        $models = [];

        foreach ($files as $file)
        {
            $models[] = pathinfo($file, PATHINFO_FILENAME);
        }

        return $models;
    }
    
    
    
    function runArtisanCommand($command, $arguments = [])
    {
        $command->setLaravel(app());
        
        $output = new Symfony\Component\Console\Output\BufferedOutput;
        
        $this->runCommand($command, $arguments, $output);
        
        return $output->fetch();
    }
    
    
    public function invokePrivateMethod(&$object, $methodName, array $parameters = array())
	{
	    $reflection = new ReflectionClass(get_class($object));
	    $method = $reflection->getMethod($methodName);
	    $method->setAccessible(true);

	    return $method->invokeArgs($object, $parameters);
	}
}
