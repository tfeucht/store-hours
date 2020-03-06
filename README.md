## Store Closed Notification

The notification at the top of the site was implemented with a block that uses the TimeCheck class to see if the store is currently closed. I know there are sometimes issues with the full page cache when blocks need to change dynamically. I noticed later that the X-Magento-Cache-Debug is always showing as MISS on the environement I set up. I think that usually means that the full page cache is not working correctly. This may have needed to be implemented using a javascript component to get around the full page cache.


## Redirect to Store Closed CMS Page

The Magento Action class fires a controller\_action\_predispatch\_... event with the action name every time a controller is dispatched. All the category and product pages should go through the category and product controllers, so I implemented this as an event observer. The observer also uses the TimeCheck class. It looks like the default redirect type is 302. Since the store is only closed temporarily this should be correct. It looks like the identifier for cms pages is the same as the url key, so a url to the page does not need to be generated. If the page is deleted or the identifier is changed, the redirect will 404.

## Admin Hours Grid

It looked like the only possibilities for this were creating 14 different fields with their own config paths, or using a serialized config field. The AbstractFieldArray field class looked similar to what I wanted to do, except it was designed to allow the used to add and delete rows. I commented out the add button and the action column in the template. The grid can use javascript templates to generate cell content. Since I did not want the weekday labels to be edited I created a block with a javascript template that just outputs the weekday label without an input field. I decided to save the times as timestamps, so the frontend model also formats them into a readable format based on the configured locale.

## Admin Hours Grid Backend Model

The backend model parses the entered dates into timestamps before saving them. Since there is not an input field for the weekdays, they don't get posted with the rest of the data. As a workaround, the field ID is the weekday code. The backend model fills in the codes before saving. Before the data is loaded, the localized weekday labels are filled in. If there is no data saved, empty rows for each weekday are generated. English weekday codes always used, so it's simple to match localized labels with existing data. Changing the first day of the week based on the configuration would require resorting the data every time it is loaded. I did not implement that feature.

## Timestamps

I decided to use timestamps to store the times. They are stored without date information, so the date is always 1-1-1970. The idea was that when the store timezone is changed the local times would adjust based on the time zone they were saved as. This should also work with multiple websites sharing the same hour config. The store would open and close at the same time in all websites, even though the local times could be different. This was more confusing than I expected. One problem I ran into is daylight saving time. Generally, you would want the local times to stay the same and the timestamps to change. Since the same timestamps might be used for multiple timezones it would be difficult to change the stored data. Because I was using a timestamp without date information when doing comparisons, the current time was not adjusted for DST either. Since neither the stored times or the current time is adjusted, this should be about the same as if they were both adjusted.

## Serialized Configs

I have not worked with serialized configurations very much. I asssumed the backend model would be used, or the serialzed data would be deserialized when they are retrieved, but it does not look like this is the case. I used the json deserializer to manually deserialize the data whenever the config is loaded. I could have created a helper to do this, but the config is only used in a couple of places, so I didn't think it was necessary.

## Hour Checking
I tried to use as few dependencies as possible for the HourCheck class to make it easy to test. Both the current timestamp and the hour config are set from the outside. Date information is removed form the current timestamp, so it can be compared to the other timestamps using comparison operators.

## Empty Fields
If an opening time is not filled in, it's assumed that the store is open from midnight of that day until the closing time. If the closing time is not filled in, it's assumed that the store is open from the opening time until the end of the day. If neither field is filled in for a weekday, it's assumed that the store is open for the entire day. This makes checking simple, but means that it's not possible to configure opening times before midnight or closing times after midnight.
