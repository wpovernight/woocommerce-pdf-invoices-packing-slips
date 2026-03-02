<?php

// Functions and constants
namespace Sabre\Uri {
    if(!function_exists('\\Sabre\\Uri\\resolve')){
        function resolve(...$args) {
            return \WPO\IPS\Vendor\Sabre\Uri\resolve(...func_get_args());
        }
    }
    if(!function_exists('\\Sabre\\Uri\\normalize')){
        function normalize(...$args) {
            return \WPO\IPS\Vendor\Sabre\Uri\normalize(...func_get_args());
        }
    }
    if(!function_exists('\\Sabre\\Uri\\parse')){
        function parse(...$args) {
            return \WPO\IPS\Vendor\Sabre\Uri\parse(...func_get_args());
        }
    }
    if(!function_exists('\\Sabre\\Uri\\build')){
        function build(...$args) {
            return \WPO\IPS\Vendor\Sabre\Uri\build(...func_get_args());
        }
    }
    if(!function_exists('\\Sabre\\Uri\\split')){
        function split(...$args) {
            return \WPO\IPS\Vendor\Sabre\Uri\split(...func_get_args());
        }
    }
    if(!function_exists('\\Sabre\\Uri\\_parse_fallback')){
        function _parse_fallback(...$args) {
            return \WPO\IPS\Vendor\Sabre\Uri\_parse_fallback(...func_get_args());
        }
    }
}
namespace Sabre\Xml\Deserializer {
    if(!function_exists('\\Sabre\\Xml\\Deserializer\\keyValue')){
        function keyValue(...$args) {
            return \WPO\IPS\Vendor\Sabre\Xml\Deserializer\keyValue(...func_get_args());
        }
    }
    if(!function_exists('\\Sabre\\Xml\\Deserializer\\enum')){
        function enum(...$args) {
            return \WPO\IPS\Vendor\Sabre\Xml\Deserializer\enum(...func_get_args());
        }
    }
    if(!function_exists('\\Sabre\\Xml\\Deserializer\\valueObject')){
        function valueObject(...$args) {
            return \WPO\IPS\Vendor\Sabre\Xml\Deserializer\valueObject(...func_get_args());
        }
    }
    if(!function_exists('\\Sabre\\Xml\\Deserializer\\repeatingElements')){
        function repeatingElements(...$args) {
            return \WPO\IPS\Vendor\Sabre\Xml\Deserializer\repeatingElements(...func_get_args());
        }
    }
    if(!function_exists('\\Sabre\\Xml\\Deserializer\\mixedContent')){
        function mixedContent(...$args) {
            return \WPO\IPS\Vendor\Sabre\Xml\Deserializer\mixedContent(...func_get_args());
        }
    }
    if(!function_exists('\\Sabre\\Xml\\Deserializer\\functionCaller')){
        function functionCaller(...$args) {
            return \WPO\IPS\Vendor\Sabre\Xml\Deserializer\functionCaller(...func_get_args());
        }
    }
}
namespace Sabre\Xml\Serializer {
    if(!function_exists('\\Sabre\\Xml\\Serializer\\enum')){
        function enum(...$args) {
            return \WPO\IPS\Vendor\Sabre\Xml\Serializer\enum(...func_get_args());
        }
    }
    if(!function_exists('\\Sabre\\Xml\\Serializer\\valueObject')){
        function valueObject(...$args) {
            return \WPO\IPS\Vendor\Sabre\Xml\Serializer\valueObject(...func_get_args());
        }
    }
    if(!function_exists('\\Sabre\\Xml\\Serializer\\repeatingElements')){
        function repeatingElements(...$args) {
            return \WPO\IPS\Vendor\Sabre\Xml\Serializer\repeatingElements(...func_get_args());
        }
    }
    if(!function_exists('\\Sabre\\Xml\\Serializer\\standardSerializer')){
        function standardSerializer(...$args) {
            return \WPO\IPS\Vendor\Sabre\Xml\Serializer\standardSerializer(...func_get_args());
        }
    }
}


namespace WPO\IPS\Vendor {

    use BrianHenryIE\Strauss\Types\AutoloadAliasInterface;

    /**
     * @see AutoloadAliasInterface
     *
     * @phpstan-type ClassAliasArray array{'type':'class',isabstract:bool,classname:string,namespace?:string,extends:string,implements:array<string>}
     * @phpstan-type InterfaceAliasArray array{'type':'interface',interfacename:string,namespace?:string,extends:array<string>}
     * @phpstan-type TraitAliasArray array{'type':'trait',traitname:string,namespace?:string,use:array<string>}
     * @phpstan-type AutoloadAliasArray array<string,ClassAliasArray|InterfaceAliasArray|TraitAliasArray>
     */
    class AliasAutoloader
    {
        private string $includeFilePath;

        /**
         * @var AutoloadAliasArray
         */
        private array $autoloadAliases = array (
  'Dompdf\\Cpdf' => 
  array (
    'type' => 'class',
    'classname' => 'Cpdf',
    'isabstract' => false,
    'namespace' => 'Dompdf',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Cpdf',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Adapter\\CPDF' => 
  array (
    'type' => 'class',
    'classname' => 'CPDF',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Adapter',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Adapter\\CPDF',
    'implements' => 
    array (
      0 => 'Dompdf\\Canvas',
    ),
  ),
  'Dompdf\\Adapter\\GD' => 
  array (
    'type' => 'class',
    'classname' => 'GD',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Adapter',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Adapter\\GD',
    'implements' => 
    array (
      0 => 'Dompdf\\Canvas',
    ),
  ),
  'Dompdf\\Adapter\\PDFLib' => 
  array (
    'type' => 'class',
    'classname' => 'PDFLib',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Adapter',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Adapter\\PDFLib',
    'implements' => 
    array (
      0 => 'Dompdf\\Canvas',
    ),
  ),
  'Dompdf\\CanvasFactory' => 
  array (
    'type' => 'class',
    'classname' => 'CanvasFactory',
    'isabstract' => false,
    'namespace' => 'Dompdf',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\CanvasFactory',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Cellmap' => 
  array (
    'type' => 'class',
    'classname' => 'Cellmap',
    'isabstract' => false,
    'namespace' => 'Dompdf',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Cellmap',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Css\\AttributeTranslator' => 
  array (
    'type' => 'class',
    'classname' => 'AttributeTranslator',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Css',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Css\\AttributeTranslator',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Css\\Color' => 
  array (
    'type' => 'class',
    'classname' => 'Color',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Css',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Css\\Color',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Css\\Content\\Attr' => 
  array (
    'type' => 'class',
    'classname' => 'Attr',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Css\\Content',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Css\\Content\\Attr',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Css\\Content\\CloseQuote' => 
  array (
    'type' => 'class',
    'classname' => 'CloseQuote',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Css\\Content',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Css\\Content\\CloseQuote',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Css\\Content\\ContentPart' => 
  array (
    'type' => 'class',
    'classname' => 'ContentPart',
    'isabstract' => true,
    'namespace' => 'Dompdf\\Css\\Content',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Css\\Content\\ContentPart',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Css\\Content\\Counter' => 
  array (
    'type' => 'class',
    'classname' => 'Counter',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Css\\Content',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Css\\Content\\Counter',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Css\\Content\\Counters' => 
  array (
    'type' => 'class',
    'classname' => 'Counters',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Css\\Content',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Css\\Content\\Counters',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Css\\Content\\NoCloseQuote' => 
  array (
    'type' => 'class',
    'classname' => 'NoCloseQuote',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Css\\Content',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Css\\Content\\NoCloseQuote',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Css\\Content\\NoOpenQuote' => 
  array (
    'type' => 'class',
    'classname' => 'NoOpenQuote',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Css\\Content',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Css\\Content\\NoOpenQuote',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Css\\Content\\OpenQuote' => 
  array (
    'type' => 'class',
    'classname' => 'OpenQuote',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Css\\Content',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Css\\Content\\OpenQuote',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Css\\Content\\StringPart' => 
  array (
    'type' => 'class',
    'classname' => 'StringPart',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Css\\Content',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Css\\Content\\StringPart',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Css\\Content\\Url' => 
  array (
    'type' => 'class',
    'classname' => 'Url',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Css\\Content',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Css\\Content\\Url',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Css\\Style' => 
  array (
    'type' => 'class',
    'classname' => 'Style',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Css',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Css\\Style',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Css\\Stylesheet' => 
  array (
    'type' => 'class',
    'classname' => 'Stylesheet',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Css',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Css\\Stylesheet',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Dompdf' => 
  array (
    'type' => 'class',
    'classname' => 'Dompdf',
    'isabstract' => false,
    'namespace' => 'Dompdf',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Dompdf',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Exception\\ImageException' => 
  array (
    'type' => 'class',
    'classname' => 'ImageException',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Exception',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Exception\\ImageException',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\FontMetrics' => 
  array (
    'type' => 'class',
    'classname' => 'FontMetrics',
    'isabstract' => false,
    'namespace' => 'Dompdf',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\FontMetrics',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Frame\\Factory' => 
  array (
    'type' => 'class',
    'classname' => 'Factory',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Frame',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Frame\\Factory',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Frame\\FrameListIterator' => 
  array (
    'type' => 'class',
    'classname' => 'FrameListIterator',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Frame',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Frame\\FrameListIterator',
    'implements' => 
    array (
      0 => 'Iterator',
    ),
  ),
  'Dompdf\\Frame\\FrameTree' => 
  array (
    'type' => 'class',
    'classname' => 'FrameTree',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Frame',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Frame\\FrameTree',
    'implements' => 
    array (
      0 => 'IteratorAggregate',
    ),
  ),
  'Dompdf\\Frame\\FrameTreeIterator' => 
  array (
    'type' => 'class',
    'classname' => 'FrameTreeIterator',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Frame',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Frame\\FrameTreeIterator',
    'implements' => 
    array (
      0 => 'Iterator',
    ),
  ),
  'Dompdf\\FrameDecorator\\AbstractFrameDecorator' => 
  array (
    'type' => 'class',
    'classname' => 'AbstractFrameDecorator',
    'isabstract' => true,
    'namespace' => 'Dompdf\\FrameDecorator',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\FrameDecorator\\AbstractFrameDecorator',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\FrameDecorator\\Block' => 
  array (
    'type' => 'class',
    'classname' => 'Block',
    'isabstract' => false,
    'namespace' => 'Dompdf\\FrameDecorator',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\FrameDecorator\\Block',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\FrameDecorator\\Image' => 
  array (
    'type' => 'class',
    'classname' => 'Image',
    'isabstract' => false,
    'namespace' => 'Dompdf\\FrameDecorator',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\FrameDecorator\\Image',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\FrameDecorator\\Inline' => 
  array (
    'type' => 'class',
    'classname' => 'Inline',
    'isabstract' => false,
    'namespace' => 'Dompdf\\FrameDecorator',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\FrameDecorator\\Inline',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\FrameDecorator\\ListBullet' => 
  array (
    'type' => 'class',
    'classname' => 'ListBullet',
    'isabstract' => false,
    'namespace' => 'Dompdf\\FrameDecorator',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\FrameDecorator\\ListBullet',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\FrameDecorator\\ListBulletImage' => 
  array (
    'type' => 'class',
    'classname' => 'ListBulletImage',
    'isabstract' => false,
    'namespace' => 'Dompdf\\FrameDecorator',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\FrameDecorator\\ListBulletImage',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\FrameDecorator\\NullFrameDecorator' => 
  array (
    'type' => 'class',
    'classname' => 'NullFrameDecorator',
    'isabstract' => false,
    'namespace' => 'Dompdf\\FrameDecorator',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\FrameDecorator\\NullFrameDecorator',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\FrameDecorator\\Page' => 
  array (
    'type' => 'class',
    'classname' => 'Page',
    'isabstract' => false,
    'namespace' => 'Dompdf\\FrameDecorator',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\FrameDecorator\\Page',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\FrameDecorator\\Table' => 
  array (
    'type' => 'class',
    'classname' => 'Table',
    'isabstract' => false,
    'namespace' => 'Dompdf\\FrameDecorator',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\FrameDecorator\\Table',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\FrameDecorator\\TableCell' => 
  array (
    'type' => 'class',
    'classname' => 'TableCell',
    'isabstract' => false,
    'namespace' => 'Dompdf\\FrameDecorator',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\FrameDecorator\\TableCell',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\FrameDecorator\\TableRow' => 
  array (
    'type' => 'class',
    'classname' => 'TableRow',
    'isabstract' => false,
    'namespace' => 'Dompdf\\FrameDecorator',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\FrameDecorator\\TableRow',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\FrameDecorator\\TableRowGroup' => 
  array (
    'type' => 'class',
    'classname' => 'TableRowGroup',
    'isabstract' => false,
    'namespace' => 'Dompdf\\FrameDecorator',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\FrameDecorator\\TableRowGroup',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\FrameDecorator\\Text' => 
  array (
    'type' => 'class',
    'classname' => 'Text',
    'isabstract' => false,
    'namespace' => 'Dompdf\\FrameDecorator',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\FrameDecorator\\Text',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\FrameReflower\\AbstractFrameReflower' => 
  array (
    'type' => 'class',
    'classname' => 'AbstractFrameReflower',
    'isabstract' => true,
    'namespace' => 'Dompdf\\FrameReflower',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\FrameReflower\\AbstractFrameReflower',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\FrameReflower\\Block' => 
  array (
    'type' => 'class',
    'classname' => 'Block',
    'isabstract' => false,
    'namespace' => 'Dompdf\\FrameReflower',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\FrameReflower\\Block',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\FrameReflower\\Image' => 
  array (
    'type' => 'class',
    'classname' => 'Image',
    'isabstract' => false,
    'namespace' => 'Dompdf\\FrameReflower',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\FrameReflower\\Image',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\FrameReflower\\Inline' => 
  array (
    'type' => 'class',
    'classname' => 'Inline',
    'isabstract' => false,
    'namespace' => 'Dompdf\\FrameReflower',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\FrameReflower\\Inline',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\FrameReflower\\ListBullet' => 
  array (
    'type' => 'class',
    'classname' => 'ListBullet',
    'isabstract' => false,
    'namespace' => 'Dompdf\\FrameReflower',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\FrameReflower\\ListBullet',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\FrameReflower\\NullFrameReflower' => 
  array (
    'type' => 'class',
    'classname' => 'NullFrameReflower',
    'isabstract' => false,
    'namespace' => 'Dompdf\\FrameReflower',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\FrameReflower\\NullFrameReflower',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\FrameReflower\\Page' => 
  array (
    'type' => 'class',
    'classname' => 'Page',
    'isabstract' => false,
    'namespace' => 'Dompdf\\FrameReflower',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\FrameReflower\\Page',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\FrameReflower\\Table' => 
  array (
    'type' => 'class',
    'classname' => 'Table',
    'isabstract' => false,
    'namespace' => 'Dompdf\\FrameReflower',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\FrameReflower\\Table',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\FrameReflower\\TableCell' => 
  array (
    'type' => 'class',
    'classname' => 'TableCell',
    'isabstract' => false,
    'namespace' => 'Dompdf\\FrameReflower',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\FrameReflower\\TableCell',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\FrameReflower\\TableRow' => 
  array (
    'type' => 'class',
    'classname' => 'TableRow',
    'isabstract' => false,
    'namespace' => 'Dompdf\\FrameReflower',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\FrameReflower\\TableRow',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\FrameReflower\\TableRowGroup' => 
  array (
    'type' => 'class',
    'classname' => 'TableRowGroup',
    'isabstract' => false,
    'namespace' => 'Dompdf\\FrameReflower',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\FrameReflower\\TableRowGroup',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\FrameReflower\\Text' => 
  array (
    'type' => 'class',
    'classname' => 'Text',
    'isabstract' => false,
    'namespace' => 'Dompdf\\FrameReflower',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\FrameReflower\\Text',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Helpers' => 
  array (
    'type' => 'class',
    'classname' => 'Helpers',
    'isabstract' => false,
    'namespace' => 'Dompdf',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Helpers',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Image\\Cache' => 
  array (
    'type' => 'class',
    'classname' => 'Cache',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Image',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Image\\Cache',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\JavascriptEmbedder' => 
  array (
    'type' => 'class',
    'classname' => 'JavascriptEmbedder',
    'isabstract' => false,
    'namespace' => 'Dompdf',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\JavascriptEmbedder',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\LineBox' => 
  array (
    'type' => 'class',
    'classname' => 'LineBox',
    'isabstract' => false,
    'namespace' => 'Dompdf',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\LineBox',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Options' => 
  array (
    'type' => 'class',
    'classname' => 'Options',
    'isabstract' => false,
    'namespace' => 'Dompdf',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Options',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\PhpEvaluator' => 
  array (
    'type' => 'class',
    'classname' => 'PhpEvaluator',
    'isabstract' => false,
    'namespace' => 'Dompdf',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\PhpEvaluator',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Positioner\\Absolute' => 
  array (
    'type' => 'class',
    'classname' => 'Absolute',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Positioner',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Positioner\\Absolute',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Positioner\\AbstractPositioner' => 
  array (
    'type' => 'class',
    'classname' => 'AbstractPositioner',
    'isabstract' => true,
    'namespace' => 'Dompdf\\Positioner',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Positioner\\AbstractPositioner',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Positioner\\Block' => 
  array (
    'type' => 'class',
    'classname' => 'Block',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Positioner',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Positioner\\Block',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Positioner\\Fixed' => 
  array (
    'type' => 'class',
    'classname' => 'Fixed',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Positioner',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Positioner\\Fixed',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Positioner\\Inline' => 
  array (
    'type' => 'class',
    'classname' => 'Inline',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Positioner',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Positioner\\Inline',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Positioner\\ListBullet' => 
  array (
    'type' => 'class',
    'classname' => 'ListBullet',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Positioner',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Positioner\\ListBullet',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Positioner\\NullPositioner' => 
  array (
    'type' => 'class',
    'classname' => 'NullPositioner',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Positioner',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Positioner\\NullPositioner',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Positioner\\TableCell' => 
  array (
    'type' => 'class',
    'classname' => 'TableCell',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Positioner',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Positioner\\TableCell',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Positioner\\TableRow' => 
  array (
    'type' => 'class',
    'classname' => 'TableRow',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Positioner',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Positioner\\TableRow',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Renderer\\AbstractRenderer' => 
  array (
    'type' => 'class',
    'classname' => 'AbstractRenderer',
    'isabstract' => true,
    'namespace' => 'Dompdf\\Renderer',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Renderer\\AbstractRenderer',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Renderer\\Block' => 
  array (
    'type' => 'class',
    'classname' => 'Block',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Renderer',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Renderer\\Block',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Renderer\\Image' => 
  array (
    'type' => 'class',
    'classname' => 'Image',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Renderer',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Renderer\\Image',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Renderer\\Inline' => 
  array (
    'type' => 'class',
    'classname' => 'Inline',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Renderer',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Renderer\\Inline',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Renderer\\ListBullet' => 
  array (
    'type' => 'class',
    'classname' => 'ListBullet',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Renderer',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Renderer\\ListBullet',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Renderer\\TableCell' => 
  array (
    'type' => 'class',
    'classname' => 'TableCell',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Renderer',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Renderer\\TableCell',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Renderer\\TableRow' => 
  array (
    'type' => 'class',
    'classname' => 'TableRow',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Renderer',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Renderer\\TableRow',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Renderer\\TableRowGroup' => 
  array (
    'type' => 'class',
    'classname' => 'TableRowGroup',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Renderer',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Renderer\\TableRowGroup',
    'implements' => 
    array (
    ),
  ),
  'Dompdf\\Renderer\\Text' => 
  array (
    'type' => 'class',
    'classname' => 'Text',
    'isabstract' => false,
    'namespace' => 'Dompdf\\Renderer',
    'extends' => 'WPO\\IPS\\Vendor\\Dompdf\\Renderer\\Text',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\AdobeFontMetrics' => 
  array (
    'type' => 'class',
    'classname' => 'AdobeFontMetrics',
    'isabstract' => false,
    'namespace' => 'FontLib',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\AdobeFontMetrics',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\BinaryStream' => 
  array (
    'type' => 'class',
    'classname' => 'BinaryStream',
    'isabstract' => false,
    'namespace' => 'FontLib',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\BinaryStream',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\EOT\\File' => 
  array (
    'type' => 'class',
    'classname' => 'File',
    'isabstract' => false,
    'namespace' => 'FontLib\\EOT',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\EOT\\File',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\EOT\\Header' => 
  array (
    'type' => 'class',
    'classname' => 'Header',
    'isabstract' => false,
    'namespace' => 'FontLib\\EOT',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\EOT\\Header',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\EncodingMap' => 
  array (
    'type' => 'class',
    'classname' => 'EncodingMap',
    'isabstract' => false,
    'namespace' => 'FontLib',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\EncodingMap',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\Exception\\FontNotFoundException' => 
  array (
    'type' => 'class',
    'classname' => 'FontNotFoundException',
    'isabstract' => false,
    'namespace' => 'FontLib\\Exception',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\Exception\\FontNotFoundException',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\Font' => 
  array (
    'type' => 'class',
    'classname' => 'Font',
    'isabstract' => false,
    'namespace' => 'FontLib',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\Font',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\Glyph\\Outline' => 
  array (
    'type' => 'class',
    'classname' => 'Outline',
    'isabstract' => false,
    'namespace' => 'FontLib\\Glyph',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\Glyph\\Outline',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\Glyph\\OutlineComponent' => 
  array (
    'type' => 'class',
    'classname' => 'OutlineComponent',
    'isabstract' => false,
    'namespace' => 'FontLib\\Glyph',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\Glyph\\OutlineComponent',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\Glyph\\OutlineComposite' => 
  array (
    'type' => 'class',
    'classname' => 'OutlineComposite',
    'isabstract' => false,
    'namespace' => 'FontLib\\Glyph',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\Glyph\\OutlineComposite',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\Glyph\\OutlineSimple' => 
  array (
    'type' => 'class',
    'classname' => 'OutlineSimple',
    'isabstract' => false,
    'namespace' => 'FontLib\\Glyph',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\Glyph\\OutlineSimple',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\Header' => 
  array (
    'type' => 'class',
    'classname' => 'Header',
    'isabstract' => true,
    'namespace' => 'FontLib',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\Header',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\OpenType\\File' => 
  array (
    'type' => 'class',
    'classname' => 'File',
    'isabstract' => false,
    'namespace' => 'FontLib\\OpenType',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\OpenType\\File',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\OpenType\\TableDirectoryEntry' => 
  array (
    'type' => 'class',
    'classname' => 'TableDirectoryEntry',
    'isabstract' => false,
    'namespace' => 'FontLib\\OpenType',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\OpenType\\TableDirectoryEntry',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\Table\\DirectoryEntry' => 
  array (
    'type' => 'class',
    'classname' => 'DirectoryEntry',
    'isabstract' => false,
    'namespace' => 'FontLib\\Table',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\Table\\DirectoryEntry',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\Table\\Table' => 
  array (
    'type' => 'class',
    'classname' => 'Table',
    'isabstract' => false,
    'namespace' => 'FontLib\\Table',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\Table\\Table',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\Table\\Type\\cmap' => 
  array (
    'type' => 'class',
    'classname' => 'cmap',
    'isabstract' => false,
    'namespace' => 'FontLib\\Table\\Type',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\Table\\Type\\cmap',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\Table\\Type\\cvt' => 
  array (
    'type' => 'class',
    'classname' => 'cvt',
    'isabstract' => false,
    'namespace' => 'FontLib\\Table\\Type',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\Table\\Type\\cvt',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\Table\\Type\\fpgm' => 
  array (
    'type' => 'class',
    'classname' => 'fpgm',
    'isabstract' => false,
    'namespace' => 'FontLib\\Table\\Type',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\Table\\Type\\fpgm',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\Table\\Type\\glyf' => 
  array (
    'type' => 'class',
    'classname' => 'glyf',
    'isabstract' => false,
    'namespace' => 'FontLib\\Table\\Type',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\Table\\Type\\glyf',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\Table\\Type\\head' => 
  array (
    'type' => 'class',
    'classname' => 'head',
    'isabstract' => false,
    'namespace' => 'FontLib\\Table\\Type',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\Table\\Type\\head',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\Table\\Type\\hhea' => 
  array (
    'type' => 'class',
    'classname' => 'hhea',
    'isabstract' => false,
    'namespace' => 'FontLib\\Table\\Type',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\Table\\Type\\hhea',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\Table\\Type\\hmtx' => 
  array (
    'type' => 'class',
    'classname' => 'hmtx',
    'isabstract' => false,
    'namespace' => 'FontLib\\Table\\Type',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\Table\\Type\\hmtx',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\Table\\Type\\kern' => 
  array (
    'type' => 'class',
    'classname' => 'kern',
    'isabstract' => false,
    'namespace' => 'FontLib\\Table\\Type',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\Table\\Type\\kern',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\Table\\Type\\loca' => 
  array (
    'type' => 'class',
    'classname' => 'loca',
    'isabstract' => false,
    'namespace' => 'FontLib\\Table\\Type',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\Table\\Type\\loca',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\Table\\Type\\maxp' => 
  array (
    'type' => 'class',
    'classname' => 'maxp',
    'isabstract' => false,
    'namespace' => 'FontLib\\Table\\Type',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\Table\\Type\\maxp',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\Table\\Type\\name' => 
  array (
    'type' => 'class',
    'classname' => 'name',
    'isabstract' => false,
    'namespace' => 'FontLib\\Table\\Type',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\Table\\Type\\name',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\Table\\Type\\nameRecord' => 
  array (
    'type' => 'class',
    'classname' => 'nameRecord',
    'isabstract' => false,
    'namespace' => 'FontLib\\Table\\Type',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\Table\\Type\\nameRecord',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\Table\\Type\\os2' => 
  array (
    'type' => 'class',
    'classname' => 'os2',
    'isabstract' => false,
    'namespace' => 'FontLib\\Table\\Type',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\Table\\Type\\os2',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\Table\\Type\\post' => 
  array (
    'type' => 'class',
    'classname' => 'post',
    'isabstract' => false,
    'namespace' => 'FontLib\\Table\\Type',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\Table\\Type\\post',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\Table\\Type\\prep' => 
  array (
    'type' => 'class',
    'classname' => 'prep',
    'isabstract' => false,
    'namespace' => 'FontLib\\Table\\Type',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\Table\\Type\\prep',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\TrueType\\Collection' => 
  array (
    'type' => 'class',
    'classname' => 'Collection',
    'isabstract' => false,
    'namespace' => 'FontLib\\TrueType',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\TrueType\\Collection',
    'implements' => 
    array (
      0 => 'Iterator',
      1 => 'Countable',
    ),
  ),
  'FontLib\\TrueType\\File' => 
  array (
    'type' => 'class',
    'classname' => 'File',
    'isabstract' => false,
    'namespace' => 'FontLib\\TrueType',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\TrueType\\File',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\TrueType\\Header' => 
  array (
    'type' => 'class',
    'classname' => 'Header',
    'isabstract' => false,
    'namespace' => 'FontLib\\TrueType',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\TrueType\\Header',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\TrueType\\TableDirectoryEntry' => 
  array (
    'type' => 'class',
    'classname' => 'TableDirectoryEntry',
    'isabstract' => false,
    'namespace' => 'FontLib\\TrueType',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\TrueType\\TableDirectoryEntry',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\WOFF\\File' => 
  array (
    'type' => 'class',
    'classname' => 'File',
    'isabstract' => false,
    'namespace' => 'FontLib\\WOFF',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\WOFF\\File',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\WOFF\\Header' => 
  array (
    'type' => 'class',
    'classname' => 'Header',
    'isabstract' => false,
    'namespace' => 'FontLib\\WOFF',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\WOFF\\Header',
    'implements' => 
    array (
    ),
  ),
  'FontLib\\WOFF\\TableDirectoryEntry' => 
  array (
    'type' => 'class',
    'classname' => 'TableDirectoryEntry',
    'isabstract' => false,
    'namespace' => 'FontLib\\WOFF',
    'extends' => 'WPO\\IPS\\Vendor\\FontLib\\WOFF\\TableDirectoryEntry',
    'implements' => 
    array (
    ),
  ),
  'Svg\\CssLength' => 
  array (
    'type' => 'class',
    'classname' => 'CssLength',
    'isabstract' => false,
    'namespace' => 'Svg',
    'extends' => 'WPO\\IPS\\Vendor\\Svg\\CssLength',
    'implements' => 
    array (
    ),
  ),
  'Svg\\DefaultStyle' => 
  array (
    'type' => 'class',
    'classname' => 'DefaultStyle',
    'isabstract' => false,
    'namespace' => 'Svg',
    'extends' => 'WPO\\IPS\\Vendor\\Svg\\DefaultStyle',
    'implements' => 
    array (
    ),
  ),
  'Svg\\Document' => 
  array (
    'type' => 'class',
    'classname' => 'Document',
    'isabstract' => false,
    'namespace' => 'Svg',
    'extends' => 'WPO\\IPS\\Vendor\\Svg\\Document',
    'implements' => 
    array (
    ),
  ),
  'Svg\\Gradient\\Stop' => 
  array (
    'type' => 'class',
    'classname' => 'Stop',
    'isabstract' => false,
    'namespace' => 'Svg\\Gradient',
    'extends' => 'WPO\\IPS\\Vendor\\Svg\\Gradient\\Stop',
    'implements' => 
    array (
    ),
  ),
  'Svg\\Style' => 
  array (
    'type' => 'class',
    'classname' => 'Style',
    'isabstract' => false,
    'namespace' => 'Svg',
    'extends' => 'WPO\\IPS\\Vendor\\Svg\\Style',
    'implements' => 
    array (
    ),
  ),
  'Svg\\Surface\\CPdf' => 
  array (
    'type' => 'class',
    'classname' => 'CPdf',
    'isabstract' => false,
    'namespace' => 'Svg\\Surface',
    'extends' => 'WPO\\IPS\\Vendor\\Svg\\Surface\\CPdf',
    'implements' => 
    array (
    ),
  ),
  'Svg\\Surface\\SurfaceCpdf' => 
  array (
    'type' => 'class',
    'classname' => 'SurfaceCpdf',
    'isabstract' => false,
    'namespace' => 'Svg\\Surface',
    'extends' => 'WPO\\IPS\\Vendor\\Svg\\Surface\\SurfaceCpdf',
    'implements' => 
    array (
      0 => 'Svg\\Surface\\SurfaceInterface',
    ),
  ),
  'Svg\\Surface\\SurfacePDFLib' => 
  array (
    'type' => 'class',
    'classname' => 'SurfacePDFLib',
    'isabstract' => false,
    'namespace' => 'Svg\\Surface',
    'extends' => 'WPO\\IPS\\Vendor\\Svg\\Surface\\SurfacePDFLib',
    'implements' => 
    array (
      0 => 'Svg\\Surface\\SurfaceInterface',
    ),
  ),
  'Svg\\Tag\\AbstractTag' => 
  array (
    'type' => 'class',
    'classname' => 'AbstractTag',
    'isabstract' => true,
    'namespace' => 'Svg\\Tag',
    'extends' => 'WPO\\IPS\\Vendor\\Svg\\Tag\\AbstractTag',
    'implements' => 
    array (
    ),
  ),
  'Svg\\Tag\\Anchor' => 
  array (
    'type' => 'class',
    'classname' => 'Anchor',
    'isabstract' => false,
    'namespace' => 'Svg\\Tag',
    'extends' => 'WPO\\IPS\\Vendor\\Svg\\Tag\\Anchor',
    'implements' => 
    array (
    ),
  ),
  'Svg\\Tag\\Circle' => 
  array (
    'type' => 'class',
    'classname' => 'Circle',
    'isabstract' => false,
    'namespace' => 'Svg\\Tag',
    'extends' => 'WPO\\IPS\\Vendor\\Svg\\Tag\\Circle',
    'implements' => 
    array (
    ),
  ),
  'Svg\\Tag\\ClipPath' => 
  array (
    'type' => 'class',
    'classname' => 'ClipPath',
    'isabstract' => false,
    'namespace' => 'Svg\\Tag',
    'extends' => 'WPO\\IPS\\Vendor\\Svg\\Tag\\ClipPath',
    'implements' => 
    array (
    ),
  ),
  'Svg\\Tag\\Ellipse' => 
  array (
    'type' => 'class',
    'classname' => 'Ellipse',
    'isabstract' => false,
    'namespace' => 'Svg\\Tag',
    'extends' => 'WPO\\IPS\\Vendor\\Svg\\Tag\\Ellipse',
    'implements' => 
    array (
    ),
  ),
  'Svg\\Tag\\Group' => 
  array (
    'type' => 'class',
    'classname' => 'Group',
    'isabstract' => false,
    'namespace' => 'Svg\\Tag',
    'extends' => 'WPO\\IPS\\Vendor\\Svg\\Tag\\Group',
    'implements' => 
    array (
    ),
  ),
  'Svg\\Tag\\Image' => 
  array (
    'type' => 'class',
    'classname' => 'Image',
    'isabstract' => false,
    'namespace' => 'Svg\\Tag',
    'extends' => 'WPO\\IPS\\Vendor\\Svg\\Tag\\Image',
    'implements' => 
    array (
    ),
  ),
  'Svg\\Tag\\Line' => 
  array (
    'type' => 'class',
    'classname' => 'Line',
    'isabstract' => false,
    'namespace' => 'Svg\\Tag',
    'extends' => 'WPO\\IPS\\Vendor\\Svg\\Tag\\Line',
    'implements' => 
    array (
    ),
  ),
  'Svg\\Tag\\LinearGradient' => 
  array (
    'type' => 'class',
    'classname' => 'LinearGradient',
    'isabstract' => false,
    'namespace' => 'Svg\\Tag',
    'extends' => 'WPO\\IPS\\Vendor\\Svg\\Tag\\LinearGradient',
    'implements' => 
    array (
    ),
  ),
  'Svg\\Tag\\Path' => 
  array (
    'type' => 'class',
    'classname' => 'Path',
    'isabstract' => false,
    'namespace' => 'Svg\\Tag',
    'extends' => 'WPO\\IPS\\Vendor\\Svg\\Tag\\Path',
    'implements' => 
    array (
    ),
  ),
  'Svg\\Tag\\Polygon' => 
  array (
    'type' => 'class',
    'classname' => 'Polygon',
    'isabstract' => false,
    'namespace' => 'Svg\\Tag',
    'extends' => 'WPO\\IPS\\Vendor\\Svg\\Tag\\Polygon',
    'implements' => 
    array (
    ),
  ),
  'Svg\\Tag\\Polyline' => 
  array (
    'type' => 'class',
    'classname' => 'Polyline',
    'isabstract' => false,
    'namespace' => 'Svg\\Tag',
    'extends' => 'WPO\\IPS\\Vendor\\Svg\\Tag\\Polyline',
    'implements' => 
    array (
    ),
  ),
  'Svg\\Tag\\RadialGradient' => 
  array (
    'type' => 'class',
    'classname' => 'RadialGradient',
    'isabstract' => false,
    'namespace' => 'Svg\\Tag',
    'extends' => 'WPO\\IPS\\Vendor\\Svg\\Tag\\RadialGradient',
    'implements' => 
    array (
    ),
  ),
  'Svg\\Tag\\Rect' => 
  array (
    'type' => 'class',
    'classname' => 'Rect',
    'isabstract' => false,
    'namespace' => 'Svg\\Tag',
    'extends' => 'WPO\\IPS\\Vendor\\Svg\\Tag\\Rect',
    'implements' => 
    array (
    ),
  ),
  'Svg\\Tag\\Shape' => 
  array (
    'type' => 'class',
    'classname' => 'Shape',
    'isabstract' => false,
    'namespace' => 'Svg\\Tag',
    'extends' => 'WPO\\IPS\\Vendor\\Svg\\Tag\\Shape',
    'implements' => 
    array (
    ),
  ),
  'Svg\\Tag\\Stop' => 
  array (
    'type' => 'class',
    'classname' => 'Stop',
    'isabstract' => false,
    'namespace' => 'Svg\\Tag',
    'extends' => 'WPO\\IPS\\Vendor\\Svg\\Tag\\Stop',
    'implements' => 
    array (
    ),
  ),
  'Svg\\Tag\\StyleTag' => 
  array (
    'type' => 'class',
    'classname' => 'StyleTag',
    'isabstract' => false,
    'namespace' => 'Svg\\Tag',
    'extends' => 'WPO\\IPS\\Vendor\\Svg\\Tag\\StyleTag',
    'implements' => 
    array (
    ),
  ),
  'Svg\\Tag\\Symbol' => 
  array (
    'type' => 'class',
    'classname' => 'Symbol',
    'isabstract' => false,
    'namespace' => 'Svg\\Tag',
    'extends' => 'WPO\\IPS\\Vendor\\Svg\\Tag\\Symbol',
    'implements' => 
    array (
    ),
  ),
  'Svg\\Tag\\Text' => 
  array (
    'type' => 'class',
    'classname' => 'Text',
    'isabstract' => false,
    'namespace' => 'Svg\\Tag',
    'extends' => 'WPO\\IPS\\Vendor\\Svg\\Tag\\Text',
    'implements' => 
    array (
    ),
  ),
  'Svg\\Tag\\UseTag' => 
  array (
    'type' => 'class',
    'classname' => 'UseTag',
    'isabstract' => false,
    'namespace' => 'Svg\\Tag',
    'extends' => 'WPO\\IPS\\Vendor\\Svg\\Tag\\UseTag',
    'implements' => 
    array (
    ),
  ),
  'Masterminds\\HTML5\\Elements' => 
  array (
    'type' => 'class',
    'classname' => 'Elements',
    'isabstract' => false,
    'namespace' => 'Masterminds\\HTML5',
    'extends' => 'WPO\\IPS\\Vendor\\Masterminds\\HTML5\\Elements',
    'implements' => 
    array (
    ),
  ),
  'Masterminds\\HTML5\\Entities' => 
  array (
    'type' => 'class',
    'classname' => 'Entities',
    'isabstract' => false,
    'namespace' => 'Masterminds\\HTML5',
    'extends' => 'WPO\\IPS\\Vendor\\Masterminds\\HTML5\\Entities',
    'implements' => 
    array (
    ),
  ),
  'Masterminds\\HTML5\\Exception' => 
  array (
    'type' => 'class',
    'classname' => 'Exception',
    'isabstract' => false,
    'namespace' => 'Masterminds\\HTML5',
    'extends' => 'WPO\\IPS\\Vendor\\Masterminds\\HTML5\\Exception',
    'implements' => 
    array (
    ),
  ),
  'Masterminds\\HTML5\\Parser\\CharacterReference' => 
  array (
    'type' => 'class',
    'classname' => 'CharacterReference',
    'isabstract' => false,
    'namespace' => 'Masterminds\\HTML5\\Parser',
    'extends' => 'WPO\\IPS\\Vendor\\Masterminds\\HTML5\\Parser\\CharacterReference',
    'implements' => 
    array (
    ),
  ),
  'Masterminds\\HTML5\\Parser\\DOMTreeBuilder' => 
  array (
    'type' => 'class',
    'classname' => 'DOMTreeBuilder',
    'isabstract' => false,
    'namespace' => 'Masterminds\\HTML5\\Parser',
    'extends' => 'WPO\\IPS\\Vendor\\Masterminds\\HTML5\\Parser\\DOMTreeBuilder',
    'implements' => 
    array (
      0 => 'Masterminds\\HTML5\\Parser\\EventHandler',
    ),
  ),
  'Masterminds\\HTML5\\Parser\\FileInputStream' => 
  array (
    'type' => 'class',
    'classname' => 'FileInputStream',
    'isabstract' => false,
    'namespace' => 'Masterminds\\HTML5\\Parser',
    'extends' => 'WPO\\IPS\\Vendor\\Masterminds\\HTML5\\Parser\\FileInputStream',
    'implements' => 
    array (
      0 => 'Masterminds\\HTML5\\Parser\\InputStream',
    ),
  ),
  'Masterminds\\HTML5\\Parser\\ParseError' => 
  array (
    'type' => 'class',
    'classname' => 'ParseError',
    'isabstract' => false,
    'namespace' => 'Masterminds\\HTML5\\Parser',
    'extends' => 'WPO\\IPS\\Vendor\\Masterminds\\HTML5\\Parser\\ParseError',
    'implements' => 
    array (
    ),
  ),
  'Masterminds\\HTML5\\Parser\\Scanner' => 
  array (
    'type' => 'class',
    'classname' => 'Scanner',
    'isabstract' => false,
    'namespace' => 'Masterminds\\HTML5\\Parser',
    'extends' => 'WPO\\IPS\\Vendor\\Masterminds\\HTML5\\Parser\\Scanner',
    'implements' => 
    array (
    ),
  ),
  'Masterminds\\HTML5\\Parser\\StringInputStream' => 
  array (
    'type' => 'class',
    'classname' => 'StringInputStream',
    'isabstract' => false,
    'namespace' => 'Masterminds\\HTML5\\Parser',
    'extends' => 'WPO\\IPS\\Vendor\\Masterminds\\HTML5\\Parser\\StringInputStream',
    'implements' => 
    array (
      0 => 'Masterminds\\HTML5\\Parser\\InputStream',
    ),
  ),
  'Masterminds\\HTML5\\Parser\\Tokenizer' => 
  array (
    'type' => 'class',
    'classname' => 'Tokenizer',
    'isabstract' => false,
    'namespace' => 'Masterminds\\HTML5\\Parser',
    'extends' => 'WPO\\IPS\\Vendor\\Masterminds\\HTML5\\Parser\\Tokenizer',
    'implements' => 
    array (
    ),
  ),
  'Masterminds\\HTML5\\Parser\\TreeBuildingRules' => 
  array (
    'type' => 'class',
    'classname' => 'TreeBuildingRules',
    'isabstract' => false,
    'namespace' => 'Masterminds\\HTML5\\Parser',
    'extends' => 'WPO\\IPS\\Vendor\\Masterminds\\HTML5\\Parser\\TreeBuildingRules',
    'implements' => 
    array (
    ),
  ),
  'Masterminds\\HTML5\\Parser\\UTF8Utils' => 
  array (
    'type' => 'class',
    'classname' => 'UTF8Utils',
    'isabstract' => false,
    'namespace' => 'Masterminds\\HTML5\\Parser',
    'extends' => 'WPO\\IPS\\Vendor\\Masterminds\\HTML5\\Parser\\UTF8Utils',
    'implements' => 
    array (
    ),
  ),
  'Masterminds\\HTML5\\Serializer\\HTML5Entities' => 
  array (
    'type' => 'class',
    'classname' => 'HTML5Entities',
    'isabstract' => false,
    'namespace' => 'Masterminds\\HTML5\\Serializer',
    'extends' => 'WPO\\IPS\\Vendor\\Masterminds\\HTML5\\Serializer\\HTML5Entities',
    'implements' => 
    array (
    ),
  ),
  'Masterminds\\HTML5\\Serializer\\OutputRules' => 
  array (
    'type' => 'class',
    'classname' => 'OutputRules',
    'isabstract' => false,
    'namespace' => 'Masterminds\\HTML5\\Serializer',
    'extends' => 'WPO\\IPS\\Vendor\\Masterminds\\HTML5\\Serializer\\OutputRules',
    'implements' => 
    array (
      0 => 'Masterminds\\HTML5\\Serializer\\RulesInterface',
    ),
  ),
  'Masterminds\\HTML5\\Serializer\\Traverser' => 
  array (
    'type' => 'class',
    'classname' => 'Traverser',
    'isabstract' => false,
    'namespace' => 'Masterminds\\HTML5\\Serializer',
    'extends' => 'WPO\\IPS\\Vendor\\Masterminds\\HTML5\\Serializer\\Traverser',
    'implements' => 
    array (
    ),
  ),
  'Sabberworm\\CSS\\CSSList\\AtRuleBlockList' => 
  array (
    'type' => 'class',
    'classname' => 'AtRuleBlockList',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS\\CSSList',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\CSSList\\AtRuleBlockList',
    'implements' => 
    array (
      0 => 'Sabberworm\\CSS\\Property\\AtRule',
    ),
  ),
  'Sabberworm\\CSS\\CSSList\\CSSBlockList' => 
  array (
    'type' => 'class',
    'classname' => 'CSSBlockList',
    'isabstract' => true,
    'namespace' => 'Sabberworm\\CSS\\CSSList',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\CSSList\\CSSBlockList',
    'implements' => 
    array (
    ),
  ),
  'Sabberworm\\CSS\\CSSList\\CSSList' => 
  array (
    'type' => 'class',
    'classname' => 'CSSList',
    'isabstract' => true,
    'namespace' => 'Sabberworm\\CSS\\CSSList',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\CSSList\\CSSList',
    'implements' => 
    array (
      0 => 'Sabberworm\\CSS\\Comment\\Commentable',
      1 => 'Sabberworm\\CSS\\CSSElement',
      2 => 'Sabberworm\\CSS\\Position\\Positionable',
    ),
  ),
  'Sabberworm\\CSS\\CSSList\\Document' => 
  array (
    'type' => 'class',
    'classname' => 'Document',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS\\CSSList',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\CSSList\\Document',
    'implements' => 
    array (
    ),
  ),
  'Sabberworm\\CSS\\CSSList\\KeyFrame' => 
  array (
    'type' => 'class',
    'classname' => 'KeyFrame',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS\\CSSList',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\CSSList\\KeyFrame',
    'implements' => 
    array (
      0 => 'Sabberworm\\CSS\\Property\\AtRule',
    ),
  ),
  'Sabberworm\\CSS\\Comment\\Comment' => 
  array (
    'type' => 'class',
    'classname' => 'Comment',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS\\Comment',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Comment\\Comment',
    'implements' => 
    array (
      0 => 'Sabberworm\\CSS\\Position\\Positionable',
      1 => 'Sabberworm\\CSS\\Renderable',
    ),
  ),
  'Sabberworm\\CSS\\OutputFormat' => 
  array (
    'type' => 'class',
    'classname' => 'OutputFormat',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\OutputFormat',
    'implements' => 
    array (
    ),
  ),
  'Sabberworm\\CSS\\OutputFormatter' => 
  array (
    'type' => 'class',
    'classname' => 'OutputFormatter',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\OutputFormatter',
    'implements' => 
    array (
    ),
  ),
  'Sabberworm\\CSS\\Parser' => 
  array (
    'type' => 'class',
    'classname' => 'Parser',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Parser',
    'implements' => 
    array (
    ),
  ),
  'Sabberworm\\CSS\\Parsing\\Anchor' => 
  array (
    'type' => 'class',
    'classname' => 'Anchor',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS\\Parsing',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Parsing\\Anchor',
    'implements' => 
    array (
    ),
  ),
  'Sabberworm\\CSS\\Parsing\\OutputException' => 
  array (
    'type' => 'class',
    'classname' => 'OutputException',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS\\Parsing',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Parsing\\OutputException',
    'implements' => 
    array (
    ),
  ),
  'Sabberworm\\CSS\\Parsing\\ParserState' => 
  array (
    'type' => 'class',
    'classname' => 'ParserState',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS\\Parsing',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Parsing\\ParserState',
    'implements' => 
    array (
    ),
  ),
  'Sabberworm\\CSS\\Parsing\\SourceException' => 
  array (
    'type' => 'class',
    'classname' => 'SourceException',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS\\Parsing',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Parsing\\SourceException',
    'implements' => 
    array (
      0 => 'Sabberworm\\CSS\\Position\\Positionable',
    ),
  ),
  'Sabberworm\\CSS\\Parsing\\UnexpectedEOFException' => 
  array (
    'type' => 'class',
    'classname' => 'UnexpectedEOFException',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS\\Parsing',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Parsing\\UnexpectedEOFException',
    'implements' => 
    array (
    ),
  ),
  'Sabberworm\\CSS\\Parsing\\UnexpectedTokenException' => 
  array (
    'type' => 'class',
    'classname' => 'UnexpectedTokenException',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS\\Parsing',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Parsing\\UnexpectedTokenException',
    'implements' => 
    array (
    ),
  ),
  'Sabberworm\\CSS\\Property\\CSSNamespace' => 
  array (
    'type' => 'class',
    'classname' => 'CSSNamespace',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS\\Property',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Property\\CSSNamespace',
    'implements' => 
    array (
      0 => 'Sabberworm\\CSS\\Property\\AtRule',
      1 => 'Sabberworm\\CSS\\Position\\Positionable',
    ),
  ),
  'Sabberworm\\CSS\\Property\\Charset' => 
  array (
    'type' => 'class',
    'classname' => 'Charset',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS\\Property',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Property\\Charset',
    'implements' => 
    array (
      0 => 'Sabberworm\\CSS\\Property\\AtRule',
      1 => 'Sabberworm\\CSS\\Position\\Positionable',
    ),
  ),
  'Sabberworm\\CSS\\Property\\Import' => 
  array (
    'type' => 'class',
    'classname' => 'Import',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS\\Property',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Property\\Import',
    'implements' => 
    array (
      0 => 'Sabberworm\\CSS\\Property\\AtRule',
      1 => 'Sabberworm\\CSS\\Position\\Positionable',
    ),
  ),
  'Sabberworm\\CSS\\Property\\KeyframeSelector' => 
  array (
    'type' => 'class',
    'classname' => 'KeyframeSelector',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS\\Property',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Property\\KeyframeSelector',
    'implements' => 
    array (
    ),
  ),
  'Sabberworm\\CSS\\Property\\Selector' => 
  array (
    'type' => 'class',
    'classname' => 'Selector',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS\\Property',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Property\\Selector',
    'implements' => 
    array (
    ),
  ),
  'Sabberworm\\CSS\\Rule\\Rule' => 
  array (
    'type' => 'class',
    'classname' => 'Rule',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS\\Rule',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Rule\\Rule',
    'implements' => 
    array (
      0 => 'Sabberworm\\CSS\\Comment\\Commentable',
      1 => 'Sabberworm\\CSS\\CSSElement',
      2 => 'Sabberworm\\CSS\\Position\\Positionable',
    ),
  ),
  'Sabberworm\\CSS\\RuleSet\\AtRuleSet' => 
  array (
    'type' => 'class',
    'classname' => 'AtRuleSet',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS\\RuleSet',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\RuleSet\\AtRuleSet',
    'implements' => 
    array (
      0 => 'Sabberworm\\CSS\\Property\\AtRule',
    ),
  ),
  'Sabberworm\\CSS\\RuleSet\\DeclarationBlock' => 
  array (
    'type' => 'class',
    'classname' => 'DeclarationBlock',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS\\RuleSet',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\RuleSet\\DeclarationBlock',
    'implements' => 
    array (
    ),
  ),
  'Sabberworm\\CSS\\RuleSet\\RuleSet' => 
  array (
    'type' => 'class',
    'classname' => 'RuleSet',
    'isabstract' => true,
    'namespace' => 'Sabberworm\\CSS\\RuleSet',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\RuleSet\\RuleSet',
    'implements' => 
    array (
      0 => 'Sabberworm\\CSS\\CSSElement',
      1 => 'Sabberworm\\CSS\\Comment\\Commentable',
      2 => 'Sabberworm\\CSS\\Position\\Positionable',
    ),
  ),
  'Sabberworm\\CSS\\Settings' => 
  array (
    'type' => 'class',
    'classname' => 'Settings',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Settings',
    'implements' => 
    array (
    ),
  ),
  'Sabberworm\\CSS\\Value\\CSSFunction' => 
  array (
    'type' => 'class',
    'classname' => 'CSSFunction',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS\\Value',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Value\\CSSFunction',
    'implements' => 
    array (
    ),
  ),
  'Sabberworm\\CSS\\Value\\CSSString' => 
  array (
    'type' => 'class',
    'classname' => 'CSSString',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS\\Value',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Value\\CSSString',
    'implements' => 
    array (
    ),
  ),
  'Sabberworm\\CSS\\Value\\CalcFunction' => 
  array (
    'type' => 'class',
    'classname' => 'CalcFunction',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS\\Value',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Value\\CalcFunction',
    'implements' => 
    array (
    ),
  ),
  'Sabberworm\\CSS\\Value\\CalcRuleValueList' => 
  array (
    'type' => 'class',
    'classname' => 'CalcRuleValueList',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS\\Value',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Value\\CalcRuleValueList',
    'implements' => 
    array (
    ),
  ),
  'Sabberworm\\CSS\\Value\\Color' => 
  array (
    'type' => 'class',
    'classname' => 'Color',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS\\Value',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Value\\Color',
    'implements' => 
    array (
    ),
  ),
  'Sabberworm\\CSS\\Value\\LineName' => 
  array (
    'type' => 'class',
    'classname' => 'LineName',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS\\Value',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Value\\LineName',
    'implements' => 
    array (
    ),
  ),
  'Sabberworm\\CSS\\Value\\PrimitiveValue' => 
  array (
    'type' => 'class',
    'classname' => 'PrimitiveValue',
    'isabstract' => true,
    'namespace' => 'Sabberworm\\CSS\\Value',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Value\\PrimitiveValue',
    'implements' => 
    array (
    ),
  ),
  'Sabberworm\\CSS\\Value\\RuleValueList' => 
  array (
    'type' => 'class',
    'classname' => 'RuleValueList',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS\\Value',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Value\\RuleValueList',
    'implements' => 
    array (
    ),
  ),
  'Sabberworm\\CSS\\Value\\Size' => 
  array (
    'type' => 'class',
    'classname' => 'Size',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS\\Value',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Value\\Size',
    'implements' => 
    array (
    ),
  ),
  'Sabberworm\\CSS\\Value\\URL' => 
  array (
    'type' => 'class',
    'classname' => 'URL',
    'isabstract' => false,
    'namespace' => 'Sabberworm\\CSS\\Value',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Value\\URL',
    'implements' => 
    array (
    ),
  ),
  'Sabberworm\\CSS\\Value\\Value' => 
  array (
    'type' => 'class',
    'classname' => 'Value',
    'isabstract' => true,
    'namespace' => 'Sabberworm\\CSS\\Value',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Value\\Value',
    'implements' => 
    array (
      0 => 'Sabberworm\\CSS\\CSSElement',
      1 => 'Sabberworm\\CSS\\Position\\Positionable',
    ),
  ),
  'Sabberworm\\CSS\\Value\\ValueList' => 
  array (
    'type' => 'class',
    'classname' => 'ValueList',
    'isabstract' => true,
    'namespace' => 'Sabberworm\\CSS\\Value',
    'extends' => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Value\\ValueList',
    'implements' => 
    array (
    ),
  ),
  'Sabre\\Uri\\InvalidUriException' => 
  array (
    'type' => 'class',
    'classname' => 'InvalidUriException',
    'isabstract' => false,
    'namespace' => 'Sabre\\Uri',
    'extends' => 'WPO\\IPS\\Vendor\\Sabre\\Uri\\InvalidUriException',
    'implements' => 
    array (
    ),
  ),
  'Sabre\\Uri\\Version' => 
  array (
    'type' => 'class',
    'classname' => 'Version',
    'isabstract' => false,
    'namespace' => 'Sabre\\Uri',
    'extends' => 'WPO\\IPS\\Vendor\\Sabre\\Uri\\Version',
    'implements' => 
    array (
    ),
  ),
  'Sabre\\Xml\\Element\\Base' => 
  array (
    'type' => 'class',
    'classname' => 'Base',
    'isabstract' => false,
    'namespace' => 'Sabre\\Xml\\Element',
    'extends' => 'WPO\\IPS\\Vendor\\Sabre\\Xml\\Element\\Base',
    'implements' => 
    array (
      0 => 'Sabre\\Xml\\Element',
    ),
  ),
  'Sabre\\Xml\\Element\\Cdata' => 
  array (
    'type' => 'class',
    'classname' => 'Cdata',
    'isabstract' => false,
    'namespace' => 'Sabre\\Xml\\Element',
    'extends' => 'WPO\\IPS\\Vendor\\Sabre\\Xml\\Element\\Cdata',
    'implements' => 
    array (
      0 => 'Sabre\\Xml\\XmlSerializable',
    ),
  ),
  'Sabre\\Xml\\Element\\Elements' => 
  array (
    'type' => 'class',
    'classname' => 'Elements',
    'isabstract' => false,
    'namespace' => 'Sabre\\Xml\\Element',
    'extends' => 'WPO\\IPS\\Vendor\\Sabre\\Xml\\Element\\Elements',
    'implements' => 
    array (
      0 => 'Sabre\\Xml\\Element',
    ),
  ),
  'Sabre\\Xml\\Element\\KeyValue' => 
  array (
    'type' => 'class',
    'classname' => 'KeyValue',
    'isabstract' => false,
    'namespace' => 'Sabre\\Xml\\Element',
    'extends' => 'WPO\\IPS\\Vendor\\Sabre\\Xml\\Element\\KeyValue',
    'implements' => 
    array (
      0 => 'Sabre\\Xml\\Element',
    ),
  ),
  'Sabre\\Xml\\Element\\Uri' => 
  array (
    'type' => 'class',
    'classname' => 'Uri',
    'isabstract' => false,
    'namespace' => 'Sabre\\Xml\\Element',
    'extends' => 'WPO\\IPS\\Vendor\\Sabre\\Xml\\Element\\Uri',
    'implements' => 
    array (
      0 => 'Sabre\\Xml\\Element',
    ),
  ),
  'Sabre\\Xml\\Element\\XmlFragment' => 
  array (
    'type' => 'class',
    'classname' => 'XmlFragment',
    'isabstract' => false,
    'namespace' => 'Sabre\\Xml\\Element',
    'extends' => 'WPO\\IPS\\Vendor\\Sabre\\Xml\\Element\\XmlFragment',
    'implements' => 
    array (
      0 => 'Sabre\\Xml\\Element',
    ),
  ),
  'Sabre\\Xml\\LibXMLException' => 
  array (
    'type' => 'class',
    'classname' => 'LibXMLException',
    'isabstract' => false,
    'namespace' => 'Sabre\\Xml',
    'extends' => 'WPO\\IPS\\Vendor\\Sabre\\Xml\\LibXMLException',
    'implements' => 
    array (
    ),
  ),
  'Sabre\\Xml\\ParseException' => 
  array (
    'type' => 'class',
    'classname' => 'ParseException',
    'isabstract' => false,
    'namespace' => 'Sabre\\Xml',
    'extends' => 'WPO\\IPS\\Vendor\\Sabre\\Xml\\ParseException',
    'implements' => 
    array (
    ),
  ),
  'Sabre\\Xml\\Reader' => 
  array (
    'type' => 'class',
    'classname' => 'Reader',
    'isabstract' => false,
    'namespace' => 'Sabre\\Xml',
    'extends' => 'WPO\\IPS\\Vendor\\Sabre\\Xml\\Reader',
    'implements' => 
    array (
    ),
  ),
  'Sabre\\Xml\\Service' => 
  array (
    'type' => 'class',
    'classname' => 'Service',
    'isabstract' => false,
    'namespace' => 'Sabre\\Xml',
    'extends' => 'WPO\\IPS\\Vendor\\Sabre\\Xml\\Service',
    'implements' => 
    array (
    ),
  ),
  'Sabre\\Xml\\Version' => 
  array (
    'type' => 'class',
    'classname' => 'Version',
    'isabstract' => false,
    'namespace' => 'Sabre\\Xml',
    'extends' => 'WPO\\IPS\\Vendor\\Sabre\\Xml\\Version',
    'implements' => 
    array (
    ),
  ),
  'Sabre\\Xml\\Writer' => 
  array (
    'type' => 'class',
    'classname' => 'Writer',
    'isabstract' => false,
    'namespace' => 'Sabre\\Xml',
    'extends' => 'WPO\\IPS\\Vendor\\Sabre\\Xml\\Writer',
    'implements' => 
    array (
    ),
  ),
  'Sabre\\Xml\\Element' => 
  array (
    'type' => 'interface',
    'interfacename' => 'Element',
    'namespace' => 'Sabre\\Xml',
    'extends' => 
    array (
      0 => 'WPO\\IPS\\Vendor\\Sabre\\Xml\\Element',
    ),
  ),
  'Sabberworm\\CSS\\Position\\Position' => 
  array (
    'type' => 'trait',
    'traitname' => 'Position',
    'namespace' => 'Sabberworm\\CSS\\Position',
    'use' => 
    array (
      0 => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Position\\Position',
    ),
  ),
  'Sabre\\Xml\\ContextStackTrait' => 
  array (
    'type' => 'trait',
    'traitname' => 'ContextStackTrait',
    'namespace' => 'Sabre\\Xml',
    'use' => 
    array (
      0 => 'WPO\\IPS\\Vendor\\Sabre\\Xml\\ContextStackTrait',
    ),
  ),
  'Dompdf\\Canvas' => 
  array (
    'type' => 'interface',
    'interfacename' => 'Canvas',
    'namespace' => 'Dompdf',
    'extends' => 
    array (
      0 => 'WPO\\IPS\\Vendor\\Dompdf\\Canvas',
    ),
  ),
  'Svg\\Surface\\SurfaceInterface' => 
  array (
    'type' => 'interface',
    'interfacename' => 'SurfaceInterface',
    'namespace' => 'Svg\\Surface',
    'extends' => 
    array (
      0 => 'WPO\\IPS\\Vendor\\Svg\\Surface\\SurfaceInterface',
    ),
  ),
  'Masterminds\\HTML5\\InstructionProcessor' => 
  array (
    'type' => 'interface',
    'interfacename' => 'InstructionProcessor',
    'namespace' => 'Masterminds\\HTML5',
    'extends' => 
    array (
      0 => 'WPO\\IPS\\Vendor\\Masterminds\\HTML5\\InstructionProcessor',
    ),
  ),
  'Masterminds\\HTML5\\Parser\\EventHandler' => 
  array (
    'type' => 'interface',
    'interfacename' => 'EventHandler',
    'namespace' => 'Masterminds\\HTML5\\Parser',
    'extends' => 
    array (
      0 => 'WPO\\IPS\\Vendor\\Masterminds\\HTML5\\Parser\\EventHandler',
    ),
  ),
  'Masterminds\\HTML5\\Parser\\InputStream' => 
  array (
    'type' => 'interface',
    'interfacename' => 'InputStream',
    'namespace' => 'Masterminds\\HTML5\\Parser',
    'extends' => 
    array (
      0 => 'WPO\\IPS\\Vendor\\Masterminds\\HTML5\\Parser\\InputStream',
    ),
  ),
  'Masterminds\\HTML5\\Serializer\\RulesInterface' => 
  array (
    'type' => 'interface',
    'interfacename' => 'RulesInterface',
    'namespace' => 'Masterminds\\HTML5\\Serializer',
    'extends' => 
    array (
      0 => 'WPO\\IPS\\Vendor\\Masterminds\\HTML5\\Serializer\\RulesInterface',
    ),
  ),
  'Sabberworm\\CSS\\CSSElement' => 
  array (
    'type' => 'interface',
    'interfacename' => 'CSSElement',
    'namespace' => 'Sabberworm\\CSS',
    'extends' => 
    array (
      0 => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\CSSElement',
    ),
  ),
  'Sabberworm\\CSS\\Comment\\Commentable' => 
  array (
    'type' => 'interface',
    'interfacename' => 'Commentable',
    'namespace' => 'Sabberworm\\CSS\\Comment',
    'extends' => 
    array (
      0 => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Comment\\Commentable',
    ),
  ),
  'Sabberworm\\CSS\\Position\\Positionable' => 
  array (
    'type' => 'interface',
    'interfacename' => 'Positionable',
    'namespace' => 'Sabberworm\\CSS\\Position',
    'extends' => 
    array (
      0 => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Position\\Positionable',
    ),
  ),
  'Sabberworm\\CSS\\Property\\AtRule' => 
  array (
    'type' => 'interface',
    'interfacename' => 'AtRule',
    'namespace' => 'Sabberworm\\CSS\\Property',
    'extends' => 
    array (
      0 => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Property\\AtRule',
    ),
  ),
  'Sabberworm\\CSS\\Renderable' => 
  array (
    'type' => 'interface',
    'interfacename' => 'Renderable',
    'namespace' => 'Sabberworm\\CSS',
    'extends' => 
    array (
      0 => 'WPO\\IPS\\Vendor\\Sabberworm\\CSS\\Renderable',
    ),
  ),
  'Sabre\\Xml\\XmlDeserializable' => 
  array (
    'type' => 'interface',
    'interfacename' => 'XmlDeserializable',
    'namespace' => 'Sabre\\Xml',
    'extends' => 
    array (
      0 => 'WPO\\IPS\\Vendor\\Sabre\\Xml\\XmlDeserializable',
    ),
  ),
  'Sabre\\Xml\\XmlSerializable' => 
  array (
    'type' => 'interface',
    'interfacename' => 'XmlSerializable',
    'namespace' => 'Sabre\\Xml',
    'extends' => 
    array (
      0 => 'WPO\\IPS\\Vendor\\Sabre\\Xml\\XmlSerializable',
    ),
  ),
);

        public function __construct()
        {
            $this->includeFilePath = __DIR__ . '/autoload_alias.php';
        }

        /**
         * @param string $class
         */
        public function autoload($class): void
        {
            if (!isset($this->autoloadAliases[$class])) {
                return;
            }
            switch ($this->autoloadAliases[$class]['type']) {
                case 'class':
                        $this->load(
                            $this->classTemplate(
                                $this->autoloadAliases[$class]
                            )
                        );
                    break;
                case 'interface':
                    $this->load(
                        $this->interfaceTemplate(
                            $this->autoloadAliases[$class]
                        )
                    );
                    break;
                case 'trait':
                    $this->load(
                        $this->traitTemplate(
                            $this->autoloadAliases[$class]
                        )
                    );
                    break;
                default:
                    // Never.
                    break;
            }
        }

        private function load(string $includeFile): void
        {
            file_put_contents($this->includeFilePath, $includeFile);
            include $this->includeFilePath;
            file_exists($this->includeFilePath) && unlink($this->includeFilePath);
        }

        /**
         * @param ClassAliasArray $class
         */
        private function classTemplate(array $class): string
        {
            $abstract = $class['isabstract'] ? 'abstract ' : '';
            $classname = $class['classname'];
            if (isset($class['namespace'])) {
                $namespace = "namespace {$class['namespace']};";
                $extends = '\\' . $class['extends'];
                $implements = empty($class['implements']) ? ''
                : ' implements \\' . implode(', \\', $class['implements']);
            } else {
                $namespace = '';
                $extends = $class['extends'];
                $implements = !empty($class['implements']) ? ''
                : ' implements ' . implode(', ', $class['implements']);
            }
            return <<<EOD
                <?php
                $namespace
                $abstract class $classname extends $extends $implements {}
                EOD;
        }

        /**
         * @param InterfaceAliasArray $interface
         */
        private function interfaceTemplate(array $interface): string
        {
            $interfacename = $interface['interfacename'];
            $namespace = isset($interface['namespace'])
            ? "namespace {$interface['namespace']};" : '';
            $extends = isset($interface['namespace'])
            ? '\\' . implode('\\ ,', $interface['extends'])
            : implode(', ', $interface['extends']);
            return <<<EOD
                <?php
                $namespace
                interface $interfacename extends $extends {}
                EOD;
        }

        /**
         * @param TraitAliasArray $trait
         */
        private function traitTemplate(array $trait): string
        {
            $traitname = $trait['traitname'];
            $namespace = isset($trait['namespace'])
            ? "namespace {$trait['namespace']};" : '';
            $uses = isset($trait['namespace'])
            ? '\\' . implode(';' . PHP_EOL . '    use \\', $trait['use'])
            : implode(';' . PHP_EOL . '    use ', $trait['use']);
            return <<<EOD
                <?php
                $namespace
                trait $traitname { 
                    use $uses; 
                }
                EOD;
        }
    }

    spl_autoload_register([ new AliasAutoloader(), 'autoload' ]);
}
