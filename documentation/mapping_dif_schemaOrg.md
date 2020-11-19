For all JSON fields currently in use in the NPDC portal the corresponding GCMD DIF-10 field(s) is listed.

JSON-LD | DIF-10 | Comment
---- | ---- | ----
name | Entry_Title
description | Summary->Abstract | Summary->Purpose could be included as well
version | Entry_ID->Version
identifier | - | An internal NPDC identifier
url | Related_URL->URL
includedInDataCatalog | -
citation | Dataset_Citation
keywords | ISO_Topic_Category
" | Ancillary_Keywords
" | Science_Keywords | Concattenated the fields with separator ' > '
temporalCoverage | Temporal_Coverage->Range_DateTime | formatted as yyyy-mm-dd,<br/>start and end separated with '/'
license | Access_Constraints & Usage_Constraints | If possible an url to a license (both to spdx.org and possible other source)
spatialCoverage | Spatial_Coverage | @type GeoShape
" | Location | @type GeoCoordinates

As the JSON-LD in the portal is expanded this list will be expanded as well.