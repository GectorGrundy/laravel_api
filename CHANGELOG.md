## 19.12.17 Release
### Added
- User Groups
- Publisher list filters in admin
- Admin's report
- Search by part of user email

### Changed
- Admin's stat filters
- Parked domain's path's statuses
- Do not show flow and availability for domains with new cloaking
- Moved getting lead status translations to frontend
- Build all js files through webpack

### Fixed
- Fast list of leads in all cabinets
- Returned copy and delete offer buttons
- Possibility to set empty user profile data
- Offer and Flow search by part of word

## 28.12.17 Release
### Added
- Support user role
- Possibility to nested log in
- API methods: support.create, support.changeProfile, support.getList, advertiser.changeProfile, advertiser.getList
- Filter leads by countries, offers, publishers and advertisers in lead.buildReport
- GlobalUserEnabledScope on `User` model
- Receive `support_id` field in publisher.changeProfile API method

## Changed
- Renamed `enterToUserCabinet`->`auth.loginAsUser`
- Renamed `returnToAdminCabinet`->`auth.logoutAsUser`
- Fallback app locale set as `ru`
- Replaced `user_id`->`user_hash` in `user_user_permissions.sync`, `user_user_permissions.getForUser`, `publisher.changeProfile` API methods

## Fixed
- `ApiLog` model: json instead of serialization , reqeust parameters for POST requests