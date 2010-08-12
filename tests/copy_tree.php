<?php
/**
 *
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 * 
 *   http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations
 * under the License.
 *
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @version //autogentag//
 * @filesource
 * @package TreeDatabaseTiein
 * @subpackage Tests
 */

require_once 'Tree/tests/copy_tree.php';

/**
 * @package TreeDatabaseTiein
 * @subpackage Tests
 */
class ezcTreeDbCopyTest extends ezcTreeCopyTest
{
    protected $tables  = array( 'materialized_path', 'nested_set', 'parent_child', 'data', 'datam' );

    protected function setUp()
    {
        static $i = 0;

        $this->tempDir = $this->createTempDir( __CLASS__ . sprintf( '_%03d_', ++$i ) ) . '/';
        try
        {
            $this->dbh = ezcDbInstance::get();
            $this->removeTables();
            $this->loadSchemas();
        }
        catch ( Exception $e )
        {
            $this->markTestSkipped( $e->getMessage() );
        }
        $this->storeFrom  = new ezcTreeDbExternalTableDataStore( $this->dbh, 'dataFrom', 'id', 'data' );
        $this->storeTo    = new ezcTreeDbExternalTableDataStore( $this->dbh, 'dataTo', 'id', 'data' );
        $this->storeFromXml = new ezcTreeXmlInternalDataStore();
        $this->storeFromMem = new ezcTreeMemoryDataStore();
        $this->storeToXml = new ezcTreeXmlInternalDataStore();
        $this->storeToMem = new ezcTreeMemoryDataStore();
    }

    protected function tearDown()
    {
        $this->removeTempDir();
    }

    private function loadSchemas()
    {
        $schema = ezcDbSchema::createFromFile( 'array', dirname( __FILE__ ) . "/files/all-types.dba" );
        $schema->writeToDb( $this->dbh );
    }

    protected function emptyTables()
    {
        $db = $this->dbh;

        foreach ( $this->tables as $table )
        {
            $q = $db->createDeleteQuery();
            $q->deleteFrom( $table );
            $s = $q->prepare();
            $s->execute();
        }
    }

    protected function removeTables()
    {
        try
        {
            foreach ( $this->tables as $table )
            {
                $this->dbh->exec( "DROP TABLE $table" );
            }
        }
        catch ( Exception $e )
        {
            // ignore
        }
    }

    public function testTreeParentChildToMaterializedPath()
    {
        $treeFrom = new ezcTreeDbParentChild( $this->dbh, 'parent_child', $this->storeFrom );
        $this->addTestData( $treeFrom );
        $treeTo = new ezcTreeDbMaterializedPath( $this->dbh, 'materialized_path', $this->storeTo );
        self::doCopyTest( $treeFrom, $treeTo );
    }

    public function testTreeParentChildToNestedSet()
    {
        $treeFrom = new ezcTreeDbParentChild( $this->dbh, 'parent_child', $this->storeFrom );
        $this->addTestData( $treeFrom );
        $treeTo = new ezcTreeDbNestedSet( $this->dbh, 'nested_set', $this->storeTo );
        self::doCopyTest( $treeFrom, $treeTo );
    }

    public function testTreeParentChildToXML()
    {
        $treeFrom = new ezcTreeDbParentChild( $this->dbh, 'parent_child', $this->storeFrom );
        $this->addTestData( $treeFrom );
        $treeTo = ezcTreeXml::create( $this->tempDir . 'testTreeParentChildToXML.xml', $this->storeToXml );
        self::doCopyTest( $treeFrom, $treeTo );
    }

    public function testTreeParentChildToMemory()
    {
        $treeFrom = new ezcTreeDbParentChild( $this->dbh, 'parent_child', $this->storeFrom );
        $this->addTestData( $treeFrom );
        $treeTo = ezcTreeMemory::create( $this->storeToMem );
        self::doCopyTest( $treeFrom, $treeTo );
    }

    public function testTreeMaterializedPathToParentChild()
    {
        $treeFrom = new ezcTreeDbMaterializedPath( $this->dbh, 'materialized_path', $this->storeFrom );
        $this->addTestData( $treeFrom );
        $treeTo = new ezcTreeDbParentChild( $this->dbh, 'parent_child', $this->storeTo );
        self::doCopyTest( $treeFrom, $treeTo );
    }

    public function testTreeMaterializedPathToNestedSet()
    {
        $treeFrom = new ezcTreeDbMaterializedPath( $this->dbh, 'materialized_path', $this->storeFrom );
        $this->addTestData( $treeFrom );
        $treeTo = new ezcTreeDbNestedSet( $this->dbh, 'nested_set', $this->storeTo );
        self::doCopyTest( $treeFrom, $treeTo );
    }

    public function testTreeNestedSetToMaterializedPath()
    {
        $treeFrom = new ezcTreeDbNestedSet( $this->dbh, 'nested_set', $this->storeFrom );
        $this->addTestData( $treeFrom );
        $treeTo = new ezcTreeDbMaterializedPath( $this->dbh, 'materialized_path', $this->storeTo );
        self::doCopyTest( $treeFrom, $treeTo );
    }

    public function testTreeNestedSetToParentChild()
    {
        $treeFrom = new ezcTreeDbNestedSet( $this->dbh, 'nested_set', $this->storeFrom );
        $this->addTestData( $treeFrom );
        $treeTo = new ezcTreeDbParentChild( $this->dbh, 'parent_child', $this->storeTo );
        self::doCopyTest( $treeFrom, $treeTo );
    }

    public static function suite()
    {
         return new PHPUnit_Framework_TestSuite( "ezcTreeDbCopyTest" );
    }
}

?>
