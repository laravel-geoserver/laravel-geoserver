<?php

namespace LaravelGeoserver\LaravelGeoserver\Tests;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

use LaravelGeoserver\LaravelGeoserver\GeoserverClient;
use LaravelGeoserver\LaravelGeoserver\LaravelGeoserverServiceProvider;
use LaravelGeoserver\LaravelGeoserver\PostGisDataStore;
use LaravelGeoserver\LaravelGeoserver\PostGisLayer;
use LaravelGeoserver\LaravelGeoserver\Style;
use LaravelGeoserver\LaravelGeoserver\Workspace;
use Orchestra\Testbench\TestCase;

class LaraverGeoserverTest extends TestCase
{
    protected $enablesPackageDiscoveries = true;

    public function setUp(): void
    {
        parent::setUp();
        GeoserverClient::create();
    }

    protected function getPackageProviders($app)
    {
        return [LaravelGeoserverServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        include_once __DIR__.'/../database/migrations/create_locations_table.php';

        (new \CreateLocationsTable())->up();
    }

    /** @test */
    public function client_instanciate_and_connects()
    {
        $this->assertInstanceOf(GeoserverClient::class, GeoserverClient::create());
    }

    /** @test */
    public function client_returns_a_product_version()
    {
        $version = GeoserverClient::getVersion('geoserver');

        $this->assertTrue(strlen($version) > 0);
    }

    /** @test */
    public function client_returns_the_available_list_of_fonts()
    {
        $fonts = GeoserverClient::getFonts();

        $this->assertTrue(count($fonts) > 0);
    }

    /** @test */
    public function workspace_and_datastores_can_be_persisted_updated_and_deleted_on_server()
    {
        $workspaceName = Str::random(32);
        $workspace = Workspace::create($workspaceName)->save();

        $this->assertInstanceOf(Workspace::class, $workspace);
        $this->assertEquals($workspaceName, $workspace->name);

        $workspaces = GeoserverClient::workspaces();
        $this->assertInstanceOf(Collection::class, $workspaces);

        $workspace = GeoserverClient::workspace($workspaceName);
        $this->assertInstanceOf(Workspace::class, $workspace);
        $this->assertEquals($workspaceName, $workspace->name);

        $olsWorkspaceName = $workspace->name;
        $newWorkspaceName = Str::random(32);
        $workspace->name = $newWorkspaceName;

        $workspace = $workspace->save();
        $this->assertInstanceOf(Workspace::class, $workspace);
        $this->assertEquals($newWorkspaceName, $workspace->name);
        $this->assertFalse(GeoserverClient::workspaceExists($olsWorkspaceName));

        $workspace->isolated = true;
        $workspace = $workspace->save();
        $this->assertInstanceOf(Workspace::class, $workspace);
        $this->assertEquals($newWorkspaceName, $workspace->name);
        $this->assertTrue($workspace->isolated);

        $datastoreName = Str::random(16);
        $datastoreDescription = Str::random(32);
        $datastoreHost = 'postgis'; // as specified in docker-compose.yml service name
        $datastorePort = 5432; // as specified in docker-compose.yml service port
        $datastoreDatabase = env('DB_DATABASE');
        $datastoreSchema = env('DB_SCHEMA');
        $datastoreUser = env('DB_USERNAME');
        $datastorePassword = env('DB_PASSWORD');


        $datastore = PostGisDataStore::create($datastoreName, $workspace, $datastoreDescription, $datastoreHost, $datastorePort, $datastoreDatabase, $datastoreSchema, $datastoreUser, $datastorePassword);

        $this->assertInstanceOf(PostGisDataStore::class, $datastore);
        $this->assertEquals($datastoreName, $datastore->name);

        $datastore = $datastore->save();
        $datastores = GeoserverClient::datastores($workspace);
        $this->assertInstanceOf(Collection::class, $datastores);
        $this->assertCount(1, $datastores);

        $this->assertInstanceOf(Collection::class, $workspace->datastores());
        $this->assertCount(1, $workspace->datastores());

        $layerName = Str::random(16);

        $layer = PostGisLayer::create($layerName, 'locations', $datastore)->save();
        $this->assertInstanceOf(PostGisLayer::class, $layer);
        $this->assertEquals($layerName, $layer->name);
        $this->assertTrue(GeoserverClient::featureTypeExists($datastore->workspace->name, $datastore->name, $layer->name));
        $this->assertFalse(GeoserverClient::featureTypeExists('non-existing', 'non-existing', 'non-existing'));
        $this->assertInstanceOf(PostGisLayer::class, GeoserverClient::featureType($datastore->workspace->name, $datastore->name, $layer->name));

        $layers = GeoserverClient::featureTypes($datastore);
        $this->assertInstanceOf(Collection::class, $layers);
        $this->assertCount(1, $layers);

        $sld100 = file_get_contents('tests/test1.0.0.sld');
        $sld110 = file_get_contents('tests/test1.1.0.sld');

        $styleName = Str::random(16);
        $style = new Style($styleName, $workspace->name);
        $style->styleContent = $sld100;

        $secondStyle = $style->save();
        $this->assertInstanceOf(Style::class, $secondStyle);

        $this->assertTrue(str_contains($secondStyle->styleContent, 'Style 1.0.0'));

        $secondStyle->styleContent = $sld110;
        $thirdStyle = $secondStyle->save();
        $this->assertInstanceOf(Style::class, $thirdStyle);
        $this->assertTrue(str_contains($thirdStyle->styleContent, 'Style 1.1.0'));

        $this->assertInstanceOf(Collection::class, $workspace->styles());
        $this->assertInstanceOf(Style::class, $workspace->style($thirdStyle->name));
        $this->assertCount(1, $workspace->styles());

        $this->assertTrue($thirdStyle->delete());
        $this->assertFalse(GeoserverClient::styleExists($styleName, $workspace->name));

        $styleName = Str::random(16);
        $style = new Style($styleName);
        $style->styleContent = $sld100;

        $secondStyle = $style->save();
        $this->assertInstanceOf(Style::class, $secondStyle);
        $this->assertTrue(str_contains($secondStyle->styleContent, 'Style 1.0.0'));

        $secondStyle->styleContent = $sld110;
        $thirdStyle = $secondStyle->save();
        $this->assertInstanceOf(Style::class, $thirdStyle);
        $this->assertTrue(str_contains($thirdStyle->styleContent, 'Style 1.1.0'));

        $styles = GeoserverClient::styles();
        $this->assertInstanceOf(Collection::class, $styles);
        // $this->assertCount(1, $styles);

        $layer = PostGisLayer::create(Str::random(16), 'locations', $datastore)->save();
        $style = new Style(Str::random(16), $workspace->name);
        $style->styleContent = $sld100;
        $style->save();

        $this->assertTrue(str_contains($style->styleContent, 'Style 1.0.0'));

        $style->styleContent = $sld110;
        $this->assertTrue(str_contains($style->save()->styleContent, 'Style 1.1.0'));

        $layer->defaultStyle = $style;

        $secondLayer = $layer->save();

        $this->assertInstanceOf(PostGisLayer::class, $secondLayer);

        $this->assertTrue(str_contains($secondLayer->defaultStyle->styleContent, 'Style 1.1.0'));

        $secondLayer->defaultStyle->styleContent = $sld100;

        $layer = $secondLayer->save();

        $this->assertTrue(str_contains($layer->defaultStyle->styleContent, 'Style 1.0.0'));

        $secondLayer->defaultStyle = $thirdStyle;
        $secondLayer->save();

        $this->assertFalse($thirdStyle->delete());

        $this->assertTrue($layer->delete());
        $this->assertFalse(GeoserverClient::featureTypeExists($workspace->name, $datastore->name, $layer->name));

        $this->assertTrue($thirdStyle->delete());

        $this->assertFalse(GeoserverClient::styleExists($styleName));

        $layerName = Str::random(16);

        $layer = PostGisLayer::create($layerName, 'locations', $datastore)->save();
        $this->assertInstanceOf(PostGisLayer::class, $layer);
        $this->assertEquals($layerName, $layer->name);

        $styleName = Str::random(16);
        $style = new Style($styleName, $workspace->name);
        $style->styleContent = $sld100;
        $style->save();

        $layer->defaultStyle = $style;
        $secondLayer = $layer->save();
        $this->assertTrue($secondLayer->defaultStyle->name == $style->name);
        $this->assertTrue($secondLayer->defaultStyle->workspace->name == $workspace->name);

        $this->assertFalse($style->delete());

        $this->assertTrue($layer->delete());
        $this->assertFalse(GeoserverClient::featureTypeExists($workspace->name, $datastore->name, $layer->name));

        $this->assertTrue($style->delete());

        $this->assertFalse(GeoserverClient::styleExists($styleName));

        $this->assertTrue($datastore->delete());
        $this->assertFalse(GeoserverClient::datastoreExists($workspace->name, $datastore->name));

        $this->assertTrue($workspace->delete());
        $this->assertFalse(GeoserverClient::workspaceExists($workspace->name));
    }
}
