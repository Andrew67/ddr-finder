import type { ArcadeLocation, ArcadeLocationApiResponse } from './all.d.ts';

/**
 * TL;DR the nearby API response is the same as the all API, but with a distance field
 */

export interface ArcadeLocationWithDistance extends ArcadeLocation {
  /**
   * Approximate distance to this location from the coordinates provided to the search query, in kilometers.
   * Maximum precision of 2 decimal digits.
   */
  distanceKm: number;
}

export interface NearbyApiResponse extends ArcadeLocationApiResponse<ArcadeLocationWithDistance> {
  /**
   * "Bounding Box" concept in GeoJSON.
   * See {@link https://www.rfc-editor.org/rfc/rfc7946#section-5}.
   * Provides information back to the frontend about the bounding box used in the search.
   */
  bbox: [southWestLongitude: number, southWestLatitude: number,
         northEastLongitude: number, northEastLatitude: number];
}
