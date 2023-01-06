<?xml version="1.0" encoding="UTF-8"?><sld:StyledLayerDescriptor xmlns="http://www.opengis.net/sld" xmlns:sld="http://www.opengis.net/sld" xmlns:gml="http://www.opengis.net/gml" xmlns:ogc="http://www.opengis.net/ogc" version="1.0.0">
  <sld:NamedLayer>
    <sld:Name>Style 1.0.0</sld:Name>
    <sld:UserStyle>
      <sld:Name>Style 1.0.0</sld:Name>
      <sld:Title>Grey Polygon</sld:Title>
      <sld:Abstract>A sample style that just prints out a grey interior with a black outline</sld:Abstract>
      <sld:FeatureTypeStyle>
        <sld:Name>name</sld:Name>
        <sld:Rule>
          <sld:Name>Rule 1</sld:Name>
          <sld:Title>Grey Fill and Black Outline</sld:Title>
          <sld:Abstract>Grey fill with a black outline 1 pixel in width</sld:Abstract>
          <sld:PolygonSymbolizer>
            <sld:Fill>
              <sld:CssParameter name="fill">#AAAAAA</sld:CssParameter>
            </sld:Fill>
            <sld:Stroke/>
          </sld:PolygonSymbolizer>
        </sld:Rule>
      </sld:FeatureTypeStyle>
    </sld:UserStyle>
  </sld:NamedLayer>
</sld:StyledLayerDescriptor>
