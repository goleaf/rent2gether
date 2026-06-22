# Inventory Deposit Integration

Lost, damaged, or not returned inventory can create a deposit deduction candidate.

The candidate must include:

- the related inventory issue;
- booking and checkout context;
- suggested deduction amount;
- reason;
- evidence when available.

The deposit flow decides whether money is deducted. Inventory itself never deducts a deposit automatically.

Guest response and dispute handling belong to the deposit/dispute flow. Inventory only syncs responsibility back after the deposit decision.

