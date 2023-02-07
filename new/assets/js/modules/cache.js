/** @type {Form[]} */
let cached = [];

/**
 * Adds element to cache
 *
 * @param {Form} newVal
 * @return {number}
 */
export const addToCache =
        newVal => cached.push(newVal)

/**
 * Finds the form in array of cached forms
 * @param elementId id of form
 * @returns {Form}
 */
export const findCachedForm =
    elementId => cached.find(obj => obj.element_id === +elementId);

/**
 * Find the consent in array of cached forms
 * @param elementId id of form
 * @param consentId id of consent
 * @param {string} consentType adults|children|foreign
 * @return Consent|undefined|null
 */
export const findConsent = (elementId, consentId, consentType) =>
    findCachedForm(elementId).consents
        .findConsent(consentId, consentType);
