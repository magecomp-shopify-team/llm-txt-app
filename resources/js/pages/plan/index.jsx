import {
    Badge,
    Banner,
    BlockStack,
    Box,
    Button,
    Card,
    InlineStack,
    Layout,
    Page,
    Text,
} from "@shopify/polaris";
import { useMemo, useState } from "react";
import { useAppBridge } from "@shopify/app-bridge-react";
import { shop_data, planConfig } from "../../app";
import { fetchApi } from "../../utils/utils";

const PlanButton = ({ type = "month", plan, isFree }) => {
    const shopify = useAppBridge();
    const { plan_id = 0 } = shop_data;
    const [loading, setLoading] = useState(false);

    const handleSelectPlan = async () => {
        try {
            setLoading(true);
            shopify.toast.show("Processing...");

            const res = await fetchApi("GET", `/api/plans/${plan}`);

            if (res?.url) {
                shopify.toast.show('Redirecting');
                const a = document.createElement("a");
                a.href = `${res?.url}`;
                a.target = "_top";
                document.body.appendChild(a);
                a.click();
                a.remove();
            } else {
                shopify.toast.show("Unable to get processing");
            }
        } catch (err) {
            console.error(err);
            shopify.toast.show("Error selecting plan");
        } finally {
            setLoading(false);
        }
    };



    const title = useMemo(() => {
        if (plan_id == plan) {
            return "Current plan";
        }
        if (isFree) {
            return "Select free plan";
        }
        if (type == "month") {
            return "Select monthly";
        }
        return "Select yearly";
    }, [plan_id, plan, type, isFree]);

    return (
        <Button
            fullWidth
            disabled={plan_id == plan}
            variant={type == "month" ? "primary" : "secondary"}
            onClick={handleSelectPlan}
            loading={loading}
        >
            {title}
        </Button>
    );
};

const PlanTitle = ({ yearPrice, monthPrice, isFree }) => {
    if (isFree) {
        return (
            <Text as="h2" variant="headingXl">
                $0.00
                <Text as="span" variant="bodyMd" tone="subdued">
                    {" "}
                    / month
                </Text>
            </Text>
        );
    }

    const yearOff = useMemo(() => {
        return parseFloat(monthPrice * 12 - yearPrice).toFixed(2);
    }, [yearPrice, monthPrice]);

    const monthText = useMemo(() => {
        return (
            <Text as="h2" variant="headingXl">
                ${monthPrice}
                <Text as="span" variant="bodyMd" tone="subdued">
                    {" "}
                    / month
                </Text>
            </Text>
        );
    }, [monthPrice]);

    const yearText = useMemo(() => {
        return (
            <Text as="h2" variant="bodyMd" tone="subdued">
                ${yearPrice}
                <Text as="span" variant="bodyMd" tone="subdued">
                    {" "}
                    / year
                </Text>{" "}
                {`($${yearOff} off)`}
            </Text>
        );
    }, [yearOff, yearPrice]);

    return (
        <>
            {monthText}
            {yearText}
        </>
    );
};

const PlanCard = ({ plan, activePlanId }) => {
    const isActive = plan.ids.includes(activePlanId);

    return (
        <Box width="30%">
            <Card>
                <Box>
                    <InlineStack align="space-between">
                        <Text as="span" variant="bodySm">
                            {plan.name}
                        </Text>
                        <InlineStack gap="200">
                            {isActive && <Badge tone="success">{"Active"}</Badge>}
                            {plan.popular && <Badge tone="magic">{"Most popular"}</Badge>}
                        </InlineStack>
                    </InlineStack>
                </Box>

                {/* Price */}
                <Box paddingBlock="400">
                    {plan.isFree ? (
                        <PlanTitle isFree />
                    ) : (
                        <PlanTitle
                            yearPrice={plan.yearly.price}
                            monthPrice={plan.monthly.price}
                        />
                    )}
                </Box>

                {/* Buttons */}
                <Box paddingBlockEnd="400">
                    <BlockStack gap="150">
                        {plan.isFree ? (
                            <PlanButton isFree />
                        ) : (
                            <>
                                <PlanButton plan={plan.ids[0]} type="month" />
                                <PlanButton plan={plan.ids[1]} type="year" />
                            </>
                        )}
                    </BlockStack>
                </Box>

                {/* Features */}
                <Box paddingBlock="400">
                    <BlockStack gap="100">
                        {plan.features.map((f, idx) => (
                            <Text key={idx} as="p" variant="bodyMd" tone="subdued">
                                {f}
                            </Text>
                        ))}
                    </BlockStack>
                </Box>
            </Card>
        </Box>
    );
};

const PlanIndex = () => {
    const { shopify_freemium = false, plan_id = 0 } = shop_data;

    const planPairs = [
        {
            name: "Basic",
            monthly: planConfig[0],
            yearly: planConfig[1],
            ids: [1, 2],
            features: [
                "Up to 50 products",
                "Up to 10 collections",
                "No pages",
                "No blogs",
            ],
        },
        {
            name: "Advance",
            monthly: planConfig[2],
            yearly: planConfig[3],
            popular: true,
            ids: [3, 4],
            features: [
                "Up to 200 products",
                "Up to 50 collections",
                "Up to 20 pages",
                "Up to 30 blogs",
            ],
        },
        {
            name: "Pro",
            monthly: planConfig[4],
            yearly: planConfig[5],
            ids: [5, 6],
            features: [
                "Unlimited products",
                "Unlimited collections",
                "Unlimited pages",
                "Unlimited blogs",
            ],
        },
    ];


    return (
        <Page title="Plan">

            <Layout>
                <Layout.Section>

                    {/* Banner: Development store */}
                    {(shopify_freemium && !plan_id) ?
                        (
                            <InlineStack align="center" gap="400">
                                <Box width="93%" paddingBlockEnd="400">
                                    <Banner title="Congratulations 🎉🥳" tone="info">
                                        <Text as="p" variant="bodyMd">
                                            Congratulations! 🎉 Development stores can now enjoy free
                                            access to{" "}
                                            <strong>the app&apos;s basic features.</strong>
                                        </Text>
                                    </Banner>
                                </Box>
                            </InlineStack>
                        ) : <></>
                    }

                    {/* Render plan cards */}
                    <InlineStack align="center" gap="400">
                        {planPairs.map((plan, i) => (
                            <PlanCard key={i} plan={plan} activePlanId={plan_id} />
                        ))}
                    </InlineStack>
                </Layout.Section>
            </Layout>

        </Page>
    );
};

export default PlanIndex;
