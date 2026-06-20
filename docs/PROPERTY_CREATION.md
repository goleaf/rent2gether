# Property Creation

`Property` represents the place that contains rooms and sleeping places. It is not the rentable unit.

Address data lives in `property_addresses` so exact location can be protected. Public guests see safe location data such as city, district, approximate coordinates, or a public location label. Exact street, house, apartment, access codes, and entry instructions are only shown after a confirmed booking when the property settings allow it.

Amenities live in `property_amenities`. Rules live in `property_rules`. Access basics live in `property_access_details`.

Property creation services enforce host ownership and keep address, amenities, rules, and access details out of Blade templates.
