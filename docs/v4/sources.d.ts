interface DataSource {
  /**
   * Short ID string for this data source, which is used in API requests/responses.
   * As of API 4.0 release, the possible values are "ziv", "navi", and "osm".
   */
  id: string;
  /** User-friendly name for the data source, such as "OpenStreetMap" */
  name: string;
  /**
   * General geographical scope of the given data source, to present to the user.
   * Value is either "world" or a two-letter ISO 3166-1 country code.
   * (See: {@link https://en.wikipedia.org/wiki/List_of_ISO_3166_country_codes})
   * Currently, the only country code used is "JP" for the "navi" data source.
   */
  scope: string;
  /** URL of the data source's website */
  "url:homepage": string;
  /**
   * URL to send users to when wanting to see information about a location at the source website.
   * Contains the special replacement strings "${id}" and "${sid}" to be replaced with those field values.
   */
  "url:info": string;
  /**
   * Mobile-friendly version URL for location information.
   * May contain the same value as the `url:info` field.
   */
  "url:info:mobile": string;
  /**
   * Whether the data source information contains availability for DDR.
   * If `false`, then all `has:ddr` fields in its locations will be `-1`.
   */
  "has:ddr": boolean;
  /**
   * Whether the data source information contains availability for PIU.
   * If `false`, then all `has:piu` fields in its locations will be `-1`.
   */
  "has:piu": boolean;
  /**
   * Whether the data source information contains availability for SMX.
   * If `false`, then all `has:smx` fields in its locations will be `-1`.
   */
  "has:smx": boolean;
}

/**
 * The full `sources.json` response.
 */
interface SourcesApiResponse {
  /** The ID of the default source to use in API queries, present to the user, etc */
  default: string;
  /** Sources, keyed by their `id` property. */
  sources: Record<string, DataSource>;
}
