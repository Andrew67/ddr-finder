/**
 * Integer used to denote availability of a specific game. Possible value ranges:
 * - &lt; -2: Reserved
 * - = -1: "Unknown", the data source does not have this information
 * - = 0: "No", there are no known machines with this game
 * - &gt;= 1: "Yes", there is at least one known machine with some version of this game
 *
 * In the future, values other than `1` may hold a meaning (number of machines or latest game version).
 * To keep implementations honest, random values will be provided in API v4.0.
 */
export type GameAvailability = number;

/**
 * Extends the concept of a "Feature" in GeoJSON, with a "Point" geometry.
 * See {@link https://www.rfc-editor.org/rfc/rfc7946#section-3.2}
 */
export interface ArcadeLocation {
  type: "Feature";
  geometry: {
    type: "Point";
    /**
     * Per standards, longitude is between -180 and 180, latitude is between -90 and 90.
     * Per {@link https://en.wikipedia.org/wiki/Decimal_degrees}, max precision will be 4 decimal digits.
     *
     * *NOTE: longitude comes **before** latitude in GeoJSON*
     */
    coordinates: [longitude: number, latitude: number];
  },
  /** Numerical ID in main database */
  id: number;
  properties: {
    /** ID of the data source the location was imported from */
    src: string;
    /** ID of the location within the data source it came from */
    sid: string;
    /** Name of the arcade */
    name: string;
    /**
     * City of arcade (e.g. "Grapevine, Texas" or "東京都").
     * Due to data source limitations, this field may be empty
     * or represent a more vague area (such as a state or prefecture).
     */
    city: string;
    /**
     * Country the arcade is located in.
     * Value is a two-letter ISO 3166-1 country code.
     * (See: {@link https://en.wikipedia.org/wiki/List_of_ISO_3166_country_codes})
     * Due to data source limitations, this field may be empty.
     */
    country: string;
    /** Game availability for DDR */
    "has:ddr": GameAvailability;
    /** Game availability for PIU */
    "has:piu": GameAvailability;
    /** Game availability for SMX */
    "has:smx": GameAvailability;
  }
}

/**
 * Extends the concept of a "FeatureCollection" in GeoJSON.
 * See {@link https://www.rfc-editor.org/rfc/rfc7946#section-3.3}.
 * It can be fed directly to GeoJSON consumers such as Google Maps and Mapbox GL JS.
 */
export interface ArcadeLocationApiResponse<T extends ArcadeLocation> {
  type: "FeatureCollection";
  features: T[];
}
