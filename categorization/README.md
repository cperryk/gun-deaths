The files here are associated with Slate's [effort](http://www.slate.com/articles/news_and_politics/crime/2013/11/gun_deaths_in_america_help_slate_dig_deeper_into_the_data.html) to categorize each gun-related death in its database with crowdsourcing.

<strong>embed.html</strong>: HTML to be embedded into an HTML component of Slate's CMS, Adobe CQ5.

<strong>index.php</strong>: Picks a random victim in the database from among the victims who have the least number of categorization responses and displays information about that victim alongside the source article of that victim and buttons that allow the user to categorize that victim based on the contextual information provided in the source article.

<strong>getCategories.php</strong>: Returns the number of victims in each category. (Just for internal analysis).

<strong>getCategorizations.php</strong>: Retrieve the categoriaton vote counts for a specific victim.

<strong>getCategorizationsCSV.php</strong>: Export categorization votes as a CSV file.