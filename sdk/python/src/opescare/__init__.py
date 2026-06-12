"""OpesCare Connect Suite Python SDK."""
from .client import OpesCareClient
from .exceptions import (
    AuthenticationError,
    AuthorizationError,
    ConsentRequiredError,
    IdempotencyConflictError,
    NotFoundException,
    OpesCareError,
    RateLimitError,
    ServerError,
    ValidationError,
    WebhookSignatureError,
)

__version__ = "1.0.0"
__all__ = [
    "OpesCareClient",
    "OpesCareError",
    "AuthenticationError",
    "AuthorizationError",
    "ConsentRequiredError",
    "ValidationError",
    "NotFoundException",
    "IdempotencyConflictError",
    "RateLimitError",
    "ServerError",
    "WebhookSignatureError",
]
