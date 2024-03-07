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

export interface NearbyApiResponse extends ArcadeLocationApiResponse<ArcadeLocationWithDistance> {}
