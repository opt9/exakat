<?php
/*
 * Copyright 2012-2015 Damien Seguy – Exakat Ltd <contact(at)exakat.io>
 * This file is part of Exakat.
 *
 * Exakat is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Exakat is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Exakat.  If not, see <http://www.gnu.org/licenses/>.
 *
 * The latest code can be found at <http://exakat.io/>.
 *
*/


namespace Tasks;

use Everyman\Neo4j\Client,
	Everyman\Neo4j\Gremlin;

class Magicnumber implements Tasks {
    private $client = null;
    
    public function run(\Config $config) {
        $project = $config->project;

        $this->client = new Client();
        
        $sqliteFile = $config->projects_root.'/projects/'.$config->project.'/magicnumber.sqlite';
        if (file_exists($sqliteFile)) {
            unlink($sqliteFile);
        }
        $sqlite = new \SQLite3($sqliteFile);

        $types = array('Integer', 'String', 'Float');

        foreach( $types as $type) {
            $query = <<<QUERY
        m = [:];
        g.idx('atoms')[['atom':'$type']].groupCount(m){it.code}.iterate();
        m.findAll()

QUERY;
            $res = $this->query($query);

            $sqlite->exec('CREATE TABLE '.$type.' (id INTEGER PRIMARY KEY, value STRING, count INTEGER)');
            $stmt = $sqlite->prepare('INSERT INTO '.$type.' (value, count) VALUES(:value, :count)');

            $total = 0;
            foreach($res as $v) {
                preg_match('/^(.*)=(\d+)/is', $v[0], $r);
                $value = $r[1];
                $count = $r[2];
                $stmt->bindValue(':value', $value, SQLITE3_TEXT);
                $stmt->bindValue(':count', $count, SQLITE3_INTEGER);
                $stmt->execute();
                $total++;
            }
            display( "$type : $total\n");
        }
    }

    private function query($query) {
        $params = array('type' => 'IN');
        $query = new Gremlin\Query($this->client, $query, $params);
        return $query->getResultSet();
    }
}

?>