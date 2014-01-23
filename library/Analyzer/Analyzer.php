<?php

namespace Analyzer;

use Everyman\Neo4j\Client,
    Everyman\Neo4j\Index\NodeIndex;

class Analyzer {
    protected $client = null;
    protected $code = null;

    protected $name = null;
    protected $description = null;
    
    protected $row_count = 0;

    private $apply_below = false;
    
    static $analyzers = array();
    
    function getDescription($lang = 'en') {
        if (is_null($this->description)) {
            $filename = "./human/$lang/".str_replace("\\", "/", str_replace("Analyzer\\", "", get_class($this))).".ini";
            
            if (!file_exists($filename)) {
                $human = array();
            } else {
                $human = parse_ini_file($filename);
            }

            if (isset($human['description'])) {
                $this->description = $human['description'];
            } else {
                $this->description = "";
            }

            if (isset($human['name'])) {
                $this->name = $human['name'];
            } else {
                $this->name = get_class($this);
            }
        }
        
        return $this->description;
    }

    function getName($lang = 'en') {
        if (is_null($this->name)) {
            $this->getDescription($lang);
        }

        return $this->name;
    }
    
    function __construct($client) {
        $this->client = $client;
        $this->methods = array();
        $this->analyzerIsNot(addslashes(get_class($this)));

        $this->queries = array();
        
        $this->code = get_class($this);
        
        
    } 
    
    static function getAnalyzers($theme) {
        return Analyzer::$analyzers[$theme];
    }
    
    function init() {
        $result = $this->query("g.getRawGraph().index().existsForNodes('analyzers');");
        if ($result[0][0] == 'false') {
            $this->query("g.createManualIndex('analyzers', Vertex)");
        }
        
        $analyzer = str_replace('\\', '\\\\', get_class($this));
        $query = "g.idx('analyzers')[['analyzer':'$analyzer']]";
        $res = $this->query($query);
        
        if (isset($res[0]) && count($res[0]) == 1) {
            print "cleaning $analyzer\n";
            $query = <<<GREMLIN
g.idx('analyzers')[['analyzer':'$analyzer']].outE('ANALYZED').each{
    g.removeEdge(it);
}

GREMLIN;
            $this->query($query);
        } else {
            print "new $analyzer\n";
            $this->code = addslashes($this->code);
            $query = <<<GREMLIN
x = g.addVertex(null, [analyzer:'$analyzer', analyzer:'true', description:'Analyzer index for $analyzer', code:'{$this->code}', fullcode:'{$this->code}',  atom:'Index', token:'T_INDEX']);

g.idx('analyzers').put('analyzer', '$analyzer', x);

g.V.has('token', 'E_CLASS')[0].each{     g.addEdge(it, x, 'CLASS'); }
g.V.has('token', 'E_FUNCTION')[0].each{     g.addEdge(it, x, 'FUNCTION'); }
g.V.has('token', 'E_NAMESPACE')[0].each{     g.addEdge(it, x, 'NAMESPACE'); }
g.V.has('token', 'E_FILE')[0].each{     g.addEdge(it, x, 'FILE'); }

GREMLIN;
            $this->query($query);
        }
    }

    // @doc return the list of dependences that must be prepared before the execution of an analyzer
    // @doc by default, nothing. 
    function dependsOn() {
        return array();
    }
    
    function setApplyBelow($apply_below = true) {
        $this->apply_below = $apply_below;
    }

    public function query($query) {
        $queryTemplate = $query;
        $params = array('type' => 'IN');
        try {
            $query = new \Everyman\Neo4j\Gremlin\Query($this->client, $queryTemplate, $params);
            return $query->getResultSet();
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $message = preg_replace('#^.*\[message\](.*?)\[exception\].*#is', '\1', $message);
            print "Exception : ".$message."\n";
            
            print $queryTemplate."\n";
            die();
        }
        return $query->getResultSet();
    }

    function _as($name) {
        $this->methods[] = 'as("'.$name.'")';
        
        return $this;
    }

    function back($name) {
        $this->methods[] = 'back("'.$name.'")';
        
        return $this;
    }

    function tokenIs($atom) {
        if (is_array($atom)) {
            $this->methods[] = 'filter{it.token in [\''.join("', '", $atom).'\']}';
        } else {
            $this->methods[] = 'has("token", "'.$atom.'")';
        }
        
        return $this;
    }

    function tokenIsNot($atom) {
        if (is_array($atom)) {
            $this->methods[] = 'filter{it.token not in [\''.join("', '", $atom).'\']}';
        } else {
            $this->methods[] = 'hasNot("token", "'.$atom.'")';
        }
        
        return $this;
    }
    
    function atomIs($atom) {
        if (is_array($atom)) {
            $this->methods[] = 'filter{it.atom in [\''.join("', '", $atom).'\']}';
        } else {
            $this->methods[] = 'has("atom", "'.$atom.'")';
        }
        
        return $this;
    }

    function classIsNot($class) {
        if (is_array($class)) {
            $this->methods[] = 'as("classIsNot").inE("CLASS").filter{it.classname not in [\''.join("', '", $class).'\']}.back("classIsNot")';
        } else {
            $this->methods[] = 'as("classIsNot").inE("CLASS").hasNot("classname", "'.$class.'").back("classIsNot")';
        }
        
        return $this;
    }
    
    function functionIs($function) {
        if (is_array($function)) {
            $this->methods[] = 'as("functionIs").inE("FUNCTION").filter{it.function in [\''.join("', '", $class).'\']}.back("functionIs")';
        } else {
            $this->methods[] = 'as("functionIs").inE("FUNCTION").has("function", "'.$function.'").back("functionIs")';
        }
        
        return $this;
    }

    function functionIsNot($function) {
        if (is_array($function)) {
            $this->methods[] = 'as("functionIsNot").inE("FUNCTION").filter{it.function not in [\''.join("', '", $function).'\']}.back("functionIsNot")';
        } else {
            $this->methods[] = 'as("functionIsNot").inE("FUNCTION").hasNot("function", "'.$function.'").back("functionIsNot")';
        }
        
        return $this;
    }
    
    function classIs($class) {
        if (is_array($class)) {
            $this->methods[] = 'as("classIs").inE("CLASS").filter{it.classname in [\''.join("', '", $class).'\']}.back("classIs")';
        } else {
//            $this->methods[] = 'filter{it.inE("CLASS").classname == "'.$class.'"}';
// @note I don't understand why filter won,t work.
            $this->methods[] = 'as("classIs").inE("CLASS").has("classname", "'.$class.'").back("classIs")';
        }
        
        return $this;
    }
    function atomInside($atom) {
        if (is_array($atom)) {
            // @todo
            die(" I don't understand arrays in atomInside()");
        } else {
            $this->methods[] = 'as("loop").out().loop("loop"){true}{it.object.atom == "'.$atom.'"}';
        }
        
        return $this;
    }
    
    function trim($property, $chars = ' ') {
        $this->methods[] = 'transform{it.code.replaceFirst("^[\'\"]?(.*?)[\'\"]?\$", "\$1")}';
    }

    function atomIsNot($atom) {
        if (is_array($atom)) {
            $this->methods[] = 'filter{!(it.atom in [\''.join("', '", $atom).'\']) }';
        } else {
            $this->methods[] = 'hasNot("atom", "'.$atom.'")';
        }
        
        return $this;
    }

    function analyzerIs($analyzer) {
        $analyzer = str_replace('\\', '\\\\', $analyzer);

        if (is_array($analyzer)) {
            $this->methods[] = 'filter{ it.analyzer in [\''.join("', '", $analyzer).'\'])}.count() != 0}';
        } else {
            $this->methods[] = 'filter{ it.in("ANALYZED").has("code", \''.$analyzer.'\').count() != 0}';
        }
        
        return $this;
    }

    function analyzerIsNot($analyzer) {
        $analyzer = str_replace('\\', '\\\\', $analyzer);

        if (is_array($analyzer)) {
            $this->methods[] = 'filter{ it.in("ANALYZED").filter{ not (it.code in [\''.join("', '", $atom).'\'])}.count() == 0}';
        } else {
            $this->methods[] = 'filter{ it.in("ANALYZED").has("code", \''.$analyzer.'\').count() == 0}';
        }

        return $this;
    }

    function is($property) {
        $this->methods[] = "has('$property', true)";

        return $this;
    }

    function isNot($property) {
        $this->methods[] = "hasNot('$property', true)";
        
        return $this;
    }

    function code($code, $caseSensitive = false) {
        if ($caseSensitive) {
            $caseSensitive = '';
        } else {
            if (is_array($code)) {
                foreach($code as $k => $v) { 
                    $code[$k] = strtolower($v); 
                }
            } else {
                $code = strtolower($code);
            }
            $caseSensitive = '.toLowerCase()';
        }
        
        if (is_array($code)) {
            // @todo
            $this->methods[] = "filter{it.code$caseSensitive in ['".join("', '", $code)."']}";
        } else {
            $this->methods[] = "filter{it.code$caseSensitive == '$code'}";
        }
        
        return $this;
    }

    function codeIsNot($code, $caseSensitive = false) {
        if ($caseSensitive) {
            $caseSensitive = '';
        } else {
            if (is_array($code)) {
                foreach($code as $k => $v) { 
                    $code[$k] = strtolower($v); 
                }
            } else {
                $code = strtolower($code);
            }
            $caseSensitive = '.toLowerCase()';
        }
        
        if (is_array($code)) {
            // @todo
            $this->methods[] = "filter{!(it.code$caseSensitive in ['".join("', '", $code)."'])}";
        } else {
            $this->methods[] = "filter{it.code$caseSensitive != '$code'}";
        }
        
        return $this;
    }

    function fullcode($code, $caseSensitive = false) {
        if ($caseSensitive) {
            $caseSensitive = '';
        } else {
            if (is_array($code)) {
                foreach($code as $k => $v) { 
                    $code[$k] = strtolower($v); 
                }
            } else {
                $code = strtolower($code);
            }
            $caseSensitive = '.toLowerCase()';
        }
        
        if (is_array($code)) {
            // @todo
            $this->methods[] = "filter{it.fullcode$caseSensitive in ['".join("', '", $code)."']}";
        } else {
            $this->methods[] = "filter{it.fullcode$caseSensitive == '$code'}";
        }
        
        return $this;
    }
    
    function fullcodeIsNot($code, $caseSensitive = false) {
        if ($caseSensitive) {
            $caseSensitive = '';
        } else {
            if (is_array($code)) {
                foreach($code as $k => $v) { 
                    $code[$k] = strtolower($v); 
                }
            } else {
                $code = strtolower($code);
            }
            $caseSensitive = '.toLowerCase()';
        }
        
        if (is_array($code)) {
            // @todo
            $this->methods[] = "filter{!(it.fullcode$caseSensitive in ['".join("', '", $code)."'])}";
        } else {
            $this->methods[] = "filter{it.fullcode$caseSensitive != '$code'}";
        }
        
        return $this;
    }

    function codeIsUppercase() {
        $this->methods[] = "filter{it.code == it.code.toUpperCase()}";
    }


    function filter($filter) {
        $this->methods[] = "filter{ $filter }";
    }

    function codeLength($length = " == 1 ") {
        // @todo add some tests ? Like Operator / value ? 
        $this->methods[] = "filter{it.code.length() $length}";
    }

    function fullcodeLength($length = " == 1 ") {
        // @todo add some tests ? Like Operator / value ? 
        $this->methods[] = "filter{it.fullcode.length() $length}";

        return $this;
    }

    function groupCount($column) {
        $this->methods[] = "groupCount(m){it.$column}";
        
        return $this;
    }

    function eachCounted($column, $times) {
        $this->methods[] = <<<GREMLIN
groupBy(m){it.$column}{it}.iterate(); 
m.findAll{ it.value.size() == $times}.values().flatten().each{ n.add(it); };
n
GREMLIN;

        return $this;
    }

    function regex($column, $regex) {
        $this->methods[] = <<<GREMLIN
filter{ (it.$column =~ "$regex" ).getCount() > 0 }
GREMLIN;

        return $this;
    }

    function out($edge_name) {
        if (is_array($edge_name)) {
            // @todo
            die(" I don't understand arrays in out()");
        } else {
            $this->methods[] = "out('$edge_name')";
        }
        
        return $this;
    }

    function in($edge_name) {
        if (is_array($edge_name)) {
            // @todo
            $this->methods[] = "inE.filter{it.label in ['".join("', '", $edge_name)."']}.outV";
        } else {
            $this->methods[] = "in('$edge_name')";
        }
        
        return $this;
    }

    function hasIn($edge_name) {
        if (is_array($edge_name)) {
            // @todo
            die(" I don't understand arrays in out()");
        } else {
            $this->methods[] = 'filter{ it.in("'.$edge_name.'").count() != 0}';
        }
        
        return $this;
    }
    
    function hasNoIn($edge_name) {
        if (is_array($edge_name)) {
            // @todo
            die(" I don't understand arrays in out() ".__METHOD__);
        } else {
            $this->methods[] = 'filter{ it.in("'.$edge_name.'").count() == 0}';
        }
        
        return $this;
    }
    
    function hasParent($parent_class, $ins = array()) {
        if (empty($ins)) {
            $in = '.in';
        } else {
            $in = array();
            
            if (!is_array($ins)) { $ins = array($ins); }
            foreach($ins as $i) {
                if (empty($i)) {
                    $in[] = ".in";
                } else {
                    $in[] = ".in('$i')";
                }
            }
            
            $in = join('', $in);
        }
        
        if (is_array($parent_class)) {
            // @todo
            die(" I don't understand arrays in hasParent() ".__METHOD__);
        } else {
            $this->methods[] = 'filter{ it.'.$in.'.has("atom", "'.$parent_class.'").count() != 0}';
        }
        
        return $this;
    }

    function hasNoParent($parent_class, $ins = array()) {
        
        if (empty($ins)) {
            $in = '.in';
        } else {
            $in = array();
            
            if (!is_array($ins)) { $ins = array($ins); }
            foreach($ins as $i) {
                if (empty($i)) {
                    $in[] = ".in";
                } else {
                    $in[] = ".in('$i')";
                }
            }
            
            $in = join('', $in);
        }
        
        if (is_array($parent_class)) {
            // @todo
            die(" I don't understand arrays in hasNoParent() ".__METHOD__);
        } else {
            $this->methods[] = 'filter{ it'.$in.'.has("atom", "'.$parent_class.'").count() == 0}';
        }
        
        return $this;
    }
    
    function run() {

        $this->analyze();
        $this->prepareQuery();

        $this->execQuery();
        
        return $this->row_count;
    }
    
    function get_row_count() {
        return $this->row_count;
    }

    function analyze() { return true; } 
    // @todo log errors when using this ? 

    function printQuery() {
        $this->prepareQuery();
        
        print_r($this->queries);
        die();
    }

    function prepareQuery() {
        // @doc This is when the object is a placeholder for others. 
        if (count($this->methods) == 1) { return true; }
        
        array_splice($this->methods, 2, 0, array('as("first")'));
        $query = join('.', $this->methods);
        
        // search what ? All ? 
        $query = <<<GREMLIN

c = 0;
m = [:];
n = [];
g.V.{$query}
GREMLIN;

        // Indexed results
        $analyzer = str_replace('\\', '\\\\', get_class($this));
        if ($this->apply_below) {
            $apply_below = <<<GREMLIN
x = it;
it.in("VALUE").            out('LOOP').out.loop(1){it.loops < 100}{it.object.code == x.code}.each{ g.addEdge(g.idx('analyzers')[['analyzer':'$analyzer']].next(), it, 'ANALYZED'); }
it.in('KEY').in("VALUE").  out('LOOP').out.loop(1){it.loops < 100}{it.object.code == x.code}.each{ g.addEdge(g.idx('analyzers')[['analyzer':'$analyzer']].next(), it, 'ANALYZED'); }
it.in('VALUE').in("VALUE").out('LOOP').out.loop(1){it.loops < 100}{it.object.code == x.code}.each{ g.addEdge(g.idx('analyzers')[['analyzer':'$analyzer']].next(), it, 'ANALYZED'); }

GREMLIN;
        } else {
            $apply_below = "";
        }
        $query .= <<<GREMLIN
.each{
    g.addEdge(g.idx('analyzers')[['analyzer':'$analyzer']].next(), it, 'ANALYZED');
    
    // Apply below
    {$apply_below}
    
    c = c + 1;
}
c;

GREMLIN;

        $this->queries[] = $query;

        $this->methods = array();
        $this->analyzerIsNot(addslashes(get_class($this)));
        
        return true;
    }
    
    function execQuery() {
        if (empty($this->queries)) { return true; }

        // @todo add a test here ? 
        foreach($this->queries as $query) {
            $r = $this->query($query);
            $this->row_count += $r[0][0];
        }

        // reset for the next
        $this->queries = array(); 
        
        // @todo multiple results ? 
        // @todo store result in the object until reading. 
        return $this->row_count;
    }
    
    function toArray() {
        $analyzer = str_replace('\\', '\\\\', get_class($this));
        $queryTemplate = "g.idx('analyzers')[['analyzer':'".$analyzer."']].out"; 
        $vertices = query($this->client, $queryTemplate);

        $report = array();
        if (count($vertices) > 0) {
            foreach($vertices as $v) {
                $report[] = $v[0]->fullcode;
            }   
        } 
        
        return $report;
    }

    function toCountedArray() {
        $analyzer = str_replace('\\', '\\\\', get_class($this));
        $queryTemplate = "m = [:]; g.idx('analyzers')[['analyzer':'".$analyzer."']].out.groupCount(m){it.fullcode}.cap"; 
        $vertices = query($this->client, $queryTemplate);

        $report = array();
        if (count($vertices) > 0) {
            foreach($vertices[0][0] as $k => $v) {
                $report[$k] = $v;
            }   
        } 
        
        return $report;
    }
}
?>