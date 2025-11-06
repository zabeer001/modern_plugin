import React, { useEffect, useState } from "react";

function toHttps(url = "") {
  return url ? url.replace(/^http:\/\//i, "https://") : "";
}

export default function Avatar({
  src,
  alt = "avatar",
  size = 36,
  className = "",
  fallbackSrc = "/placeholder-avatar.png", // change to your own placeholder path
  referrerPolicy = "no-referrer",
}) {
  // normalize to https up-front
  const [currentSrc, setCurrentSrc] = useState(toHttps(src));

  // if parent updates `src`, refresh the image state
  useEffect(() => {
    setCurrentSrc(toHttps(src));
  }, [src]);

  const handleError = () => {
    // if we already tried the real image, swap to fallback (if provided)
    if (fallbackSrc && currentSrc !== fallbackSrc) {
      setCurrentSrc(fallbackSrc);
    } else {
      // final state: render the empty block
      setCurrentSrc("");
    }
  };

  // no usable src → render the styled empty avatar block
  if (!currentSrc) {
    return (
      <span
        className={`avatar ${className}`}
        aria-hidden="true"
        style={{ width: size, height: size }}
      />
    );
  }

  return (
    <img
      className={`avatar ${className}`}
      src={currentSrc}
      alt={alt}
      width={size}
      height={size}
      loading="lazy"
      decoding="async"
      referrerPolicy={referrerPolicy}
      onError={handleError}
      // uncomment if your CSP/proxy requires it:
      // crossOrigin="anonymous"
      style={{ width: size, height: size }}
    />
  );
}
